<?php

namespace Drupal\search_api_pantheon\Services;

use Drupal\Component\FileSystem\FileSystem;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api_solr\Controller\SolrConfigSetController;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientInterface as PSR18Interface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Posting schema for Pantheon-specific solr driver.
 *
 * @package Drupal\search_api_pantheon
 */
class SchemaPoster implements LoggerAwareInterface {

  use LoggerAwareTrait;
  use StringTranslationTrait;

  /**
   * Verbose debugging.
   *
   * @var bool
   */
  protected bool $verbose = FALSE;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \Psr\Http\Client\ClientInterface
   */
  protected PSR18Interface $client;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Class Constructor.
   */
  public function __construct(
        LoggerChannelFactoryInterface $logger_factory,
        PantheonGuzzle $client,
        EntityTypeManagerInterface $entity_type_manager
    ) {
    $this->logger = $logger_factory->get('PantheonSearch');
    $this->client = $client;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Post a schema file to the Pantheon Solr server.
   *
   * @param string $server_id
   *   Search Api Server ID.
   * @param array $files
   *   Array of files to post.
   *
   * @return array
   *   Message to be displayed to user (type, message).
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function postSchema(string $server_id, $files = []): array {
    // PANTHEON Environment.
    if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
      if (!$files) {
        $files = $this->getSolrFiles($server_id);
      }
      $response = $this->uploadSchemaFiles($files);
    }
    // LOCAL DOCKER.
    if (isset($_SERVER['ENV']) && $_SERVER['ENV'] === 'local') {
      $response = $this->uploadSchemaAsZip($server_id);
    }
    if (!$response instanceof Response) {
      throw new \Exception('Cannot post schema to environment url.');
    }

    return $this->processResponse($response);
  }

  /**
   * Process response and return message to be shown to user.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   Response from Guzzle.
   *
   * @return array
   *   Message to be displayed to user (type, message).
   */
  public function processResponse(Response $response): array {
    $log_function = in_array($response->getStatusCode(), [
      200,
      201,
      202,
      203,
      204,
    ]) ? 'info' : 'error';
    $this->logger->{$log_function}('Files uploaded: {status_code} {reason}', [
      'status_code' => $response->getStatusCode(),
      'reason' => $response->getReasonPhrase(),
    ]);
    $message = vsprintf($this->t('Result: %s Status code: %d - %s'), [
      $log_function == 'error' ? 'NOT UPLOADED' : 'UPLOADED',
      $response->getStatusCode(),
      $response->getReasonPhrase(),
    ]);
    return [$log_function, $message];
  }

  /**
   * Upload schema files to server.
   *
   * @param array $schemaFiles
   *   A key => value paired array of filenames => file_contents.
   *
   * @return \Psr\Http\Message\ResponseInterface|null
   *   A PSR-7 response object from the API call.
   */
  public function uploadSchemaFiles(array $schemaFiles): ?ResponseInterface {
    // Schema upload URL.
    $uri = new Uri(
          $this->getClient()
            ->getEndpoint()
            ->getSchemaUploadUri()
      );
    $this->logger->debug('Upload url: ' . (string) $uri);
    // Build the files array.
    $toSend = ['files' => []];
    foreach ($schemaFiles as $filename => $file_contents) {
      $this->logger->info($this->t('Encoding file: {filename}'), [
            'filename' => $filename,
        ]);
      $toSend['files'][] = [
            'filename' => $filename,
            'content' => base64_encode($file_contents),
        ];
    }

    // Send the request.
    $request = new Request(
          'POST',
          $uri,
          [
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',
          ],
          json_encode($toSend)
      );
    $response = $this->getClient()->sendRequest($request);

    // Parse the response.
    $log_function = in_array($response->getStatusCode(), [200, 201, 202, 203])
            ? 'info'
            : 'error';
    $this->logger->{$log_function}($this->t('Files uploaded: {status_code} {reason}'), [
          'status_code' => $response->getStatusCode(),
          'reason' => $response->getReasonPhrase(),
      ]);
    return $response;
  }

  /**
   * Get Pantheon Client instance.
   *
   * @return \Psr\Http\Client\ClientInterface
   *   Pantheon Guzzle Client.
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * Set Pantheon Client Instance.
   *
   * @param \Psr\Http\Client\ClientInterface $client
   *   Pantheon Guzzle Client.
   */
  public function setClient(ClientInterface $client): void {
    $this->client = $client;
  }

  /**
   * Upload one at a time to docker-compose's Solr instance.
   *
   * @param string $server_id
   *   Server ID to upload to.
   *
   * @return array
   *   Status messages from each of the calls.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
    // @codingStandardsIgnoreLine

  /**
   * Get the schema and config files for posting on the solr server.
   *
   * @param string $server_id
   *   The Search API server id. Typically, `pantheon_solr8`.
   *
   * @return array
   *   Array of key-value pairs: 'filename' => 'file contents'.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \Exception
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getSolrFiles(string $server_id = 'pantheon_solr8') {
    /** @var \Drupal\search_api\ServerInterface $server */
    $server = $this->entityTypeManager
      ->getStorage('search_api_server')
      ->load($server_id);

    if (!$server instanceof EntityInterface) {
      throw new \Exception(
            'cannot retrieve the solr server connection settings from the database'
        );
    }
    $solr_configset_controller = new SolrConfigSetController();
    $solr_configset_controller->setServer($server);

    return $solr_configset_controller->getConfigFiles();
  }

  /**
   * Upload the schema files as zipped archive.
   */
  public function uploadSchemaAsZip(string $server_id): Response {
    $path_to_zip = $this->getSolrFilesAsZip($server_id);
    return $this->client->put(
          $this->client->getEndpoint()->getBaseUri() . 'api/core/configs/_default',
          [
              'query' => [
                  'action' => 'UPLOAD',
                  'name' => '_default',
                  'overwrite' => 'TRUE',
                  'configSet' => '_default',
                  'create' => 'TRUE',
              ],
              'body' => Utils::tryFopen($path_to_zip, 'r'),
              'headers' => [
                  'Content-Type' => 'application/octet-stream',
              ],
          ]
      );
  }

  /**
   * Get the solr schema files as a zip archive.
   */
  public function getSolrFilesAsZip(string $server_id) {
    $files = $this->getSolrFiles($server_id);
    $temp_dir =
            FileSystem::getOsTemporaryDirectory() .
            DIRECTORY_SEPARATOR .
            uniqid('search_api_pantheon-');
    $zip_archive = new \ZipArchive();
    $zip_archive->open($temp_dir . '.zip', \ZipArchive::CREATE);
    foreach ($files as $filename => $file_contents) {
      $zip_archive->addFromString($filename, $file_contents);
    }
    $zip_archive->close();
    return $temp_dir . '.zip';
  }

  /**
   * View a schema file on the pantheon solr server.
   *
   * @param string $filename
   *   The filename to view. Default is Schema.xml.
   *
   * @return string|null
   *   The text of the file or null on error or if the file doesn't exist.
   */
  public function viewSchema(string $filename = 'schema.xml'): ?string {
    try {
      $uri = (new Uri(
            $this->getClient()
              ->getEndpoint()
              ->getCoreBaseUri() . 'admin/file'
        ))->withQuery(
            http_build_query([
                'action' => 'VIEW',
                'file' => $filename,
            ])
        );
      $this->logger->debug('Upload url: ' . $uri);
      $request = new Request('GET', $uri);
      $response = $this->client->sendRequest($request);
      $message = vsprintf($this->t('File: %s, Status code: %d - %s'), [
            'filename' => $filename,
            'status_code' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
        ]);
      $this->logger->debug($message);

      return $response->getBody();
    }
    catch (\Throwable $e) {
      $message = vsprintf($this->t('File: %s, Status code: %d - %s'), [
            'filename' => $filename,
            'status_code' => $e->getCode(),
            'reason' => $e->getMessage(),
        ]);
      $this->logger->error($message);
    }

    return NULL;
  }

  public function uploadOneAtATime(string $server_id) {
    $schemaFiles = $this->getSolrFiles($server_id);
    $toReturn = [];
    foreach ($schemaFiles as $filename => $contents) {
      $contentType =
                substr($filename, 0, -3) == '.xml' ? 'application/xml' : 'text/plain';
      $response = $this->getClient()->post(
            $this->getClient()
              ->getEndpoint()
              ->getSchemaUploadUri(),
            [
                'query' => [
                    'action' => 'UPLOAD',
                    'name' => '_default',
                    'filePath' => $filename,
                    'contentType' => $contentType,
                    'overwrite' => 'true',
                ],
                'headers' => [
                    'Content-Type' => 'application/octet-stream',
                ],
                'body' => $contents,
            ]
        );
      // Parse the response.
      $log_function = in_array($response->getStatusCode(), [200, 201, 202, 203])
                ? 'info'
                : 'error';
      $message = vsprintf($this->t('File: %s, Status code: %d - %s'), [
            'filename' => $filename,
            'status_code' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
        ]);
      $this->logger->{$log_function}($message);

      $toReturn[] = $message;
    }
    return $toReturn;
  }

  /**
   * Get Logger Instance.
   *
   * @return \Psr\Log\LoggerInterface
   *   Drupal's Logger Interface.
   */
  public function getLogger() {
    return $this->logger;
  }

  /**
   * Set Logger Instance.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Drupal's Logger Interface.
   */
  public function setLogger(LoggerInterface $logger): void {
    $this->logger = $logger;
  }

  /**
   * Get verbosity.
   *
   * @return bool
   *   Whether or not to turn on long debugging.
   */
  protected function isVerbose(): bool {
    return $this->verbose;
  }

  /**
   * Set Verbosity.
   *
   * @param bool $isVerbose
   *   Verbosity value.
   */
  public function setVerbose(bool $isVerbose): void {
    $this->verbose = $isVerbose;
  }

}
