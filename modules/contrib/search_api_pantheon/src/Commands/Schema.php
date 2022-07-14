<?php

namespace Drupal\search_api_pantheon\Commands;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\search_api_pantheon\Plugin\SolrConnector\PantheonSolrConnector;
use Drupal\search_api_pantheon\Services\PantheonGuzzle;
use Drupal\search_api_pantheon\Services\SchemaPoster;
use Drush\Commands\DrushCommands;
use Symfony\Component\Finder\Finder;

/**
 * Drush Search Api Pantheon Schema Commands.
 */
class Schema extends DrushCommands {
  use LoggerChannelTrait;

  /**
   * Configured pantheon-solr-specific guzzle client.
   *
   * @var \Drupal\search_api_pantheon\Services\PantheonGuzzle
   */
  private PantheonGuzzle $pantheonGuzzle;

  /**
   * Configured pantheon-solr-specific schema poster class.
   *
   * @var \Drupal\search_api_pantheon\Services\SchemaPoster
   */
  private SchemaPoster $schemaPoster;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Injected by container.
   * @param \Drupal\search_api_pantheon\Services\PantheonGuzzle $pantheonGuzzle
   *   Injected by container.
   * @param \Drupal\search_api_pantheon\Services\SchemaPoster $schemaPoster
   *   Injected by Container.
   */
  public function __construct(
        LoggerChannelFactoryInterface $loggerChannelFactory,
        PantheonGuzzle $pantheonGuzzle,
        SchemaPoster $schemaPoster
    ) {
    $this->logger = $loggerChannelFactory->get('SearchAPIPantheon Drush');
    $this->pantheonGuzzle = $pantheonGuzzle;
    $this->schemaPoster = $schemaPoster;
  }

  /**
   * Search_api_pantheon:postSchema.
   *
   * @usage search-api-pantheon:postSchema [server_id] [path]
   *   Post the latest schema to the given Server.
   *   Default server ID = pantheon_solr8.
   *   Default path = empty (build files using search_api_solr mechanism).
   *
   * @command search-api-pantheon:postSchema
   *
   * @param $server_id
   *   Server id to post schema for.
   * @param $path
   *   Path to schema files (Leave empty to use default schema).
   *
   * @aliases sapps
   */
  public function postSchema(?string $server_id = NULL, ?string $path = NULL) {
    if (!$server_id) {
      $server_id = PantheonSolrConnector::getDefaultEndpoint();
    }
    try {
      $files = [];
      if ($path) {
        if (!is_dir($path)) {
          throw new \Exception("Path '$path' is not a directory.");
        }
        $finder = new Finder();
        // Only work with direct children.
        $finder->depth('== 0');
        $finder->files()->in($path);
        if (!$finder->hasResults()) {
          throw new \Exception("Path '$path' does not contain any files.");
        }
        foreach ($finder as $file) {
          $files[$file->getfilename()] = $file->getContents();
        }
      }

      $this->schemaPoster->postSchema($server_id, $files);
    }
    catch (\Exception $e) {
      $this->logger()->error((string) $e);
    }
  }

  /**
   * View a Schema File.
   *
   * @param string $filename
   *   Filename to retrieve.
   *
   * @command search-api-pantheon:view-schema
   * @aliases sapvs
   * @usage sapvs schema.xml
   * @usage search-api-pantheon:view-schema elevate.xml
   *
   * @throws \Exception
   * @throws \Psr\Http\Client\ClientExceptionInterface
   */
  public function viewSchema(string $filename = 'schema.xml') {
    $currentSchema = $this->schemaPoster->viewSchema($filename);
    $this->logger()->notice($currentSchema);
  }

}
