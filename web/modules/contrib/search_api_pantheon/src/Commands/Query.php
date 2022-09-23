<?php

namespace Drupal\search_api_pantheon\Commands;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\search_api_pantheon\Services\Endpoint;
use Drupal\search_api_pantheon\Services\PantheonGuzzle;
use Drupal\search_api_pantheon\Services\SolariumClient;
use Drush\Commands\DrushCommands;
use Solarium\Core\Query\Result\ResultInterface;
use Drupal\search_api\Entity\Server;

/**
 * A Drush command file.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class Query extends DrushCommands {

  protected PantheonGuzzle $pantheonGuzzle;
  protected Endpoint $endpoint;
  protected SolariumClient $solr;

  /**
   * Class Constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Injected by container.
   * @param \Drupal\search_api_pantheon\Services\PantheonGuzzle $pantheonGuzzle
   *   Injected by container.
   * @param \Drupal\search_api_pantheon\Services\Endpoint $endpoint
   *   Injected by container.
   * @param \Drupal\search_api_pantheon\Services\SolariumClient $solariumClient
   *   Injected by container.
   */
  public function __construct(
        LoggerChannelFactoryInterface $loggerChannelFactory,
        PantheonGuzzle $pantheonGuzzle,
        Endpoint $endpoint,
        SolariumClient $solariumClient
    ) {
    $this->logger = $loggerChannelFactory->get('SearchAPIPantheon Drush');
    $this->pantheonGuzzle = $pantheonGuzzle;
    $this->endpoint = $endpoint;
    $this->solr = $solariumClient;
  }

  /**
   * Search_api_pantheon:select.
   *
   * @usage search-api-pantheon:select <query>
   *   Runs a select query against Pantheon Solr.
   *
   * @command search-api-pantheon:select
   *
   * @option wt Output format
   * @option rows Number of rows to return
   * @option qf Query fields
   * @option defType Default search type
   * @option omitHeader Do not output header
   * @option fields Fields to return
   *
   * @aliases saps
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \JsonException
   * @throws \Exception
   */
  public function select($query, $options = [
    'wt' => 'json',
    'rows' => 10,
    'qf' => '',
    'defType' => 'edismax',
    'omitHeader' => 'true',
    'fields' => 'ss_search_api_id,ss_search_api_language,score,hash',
  ]) {
    $this->logger->notice('Running a select query against Pantheon Solr.');

    $this->logger->notice('Query: ' . urldecode($query));
    $options['query'] = urldecode($query);

    $query_object = $this->solr->createSelect($options);
    $query_object->setResponseWriter($options['wt']);

    if ($options['defType']) {
      $query_object->addParam('defType', $options['defType']);
    }
    if ($options['omitHeader']) {
      $query_object->setOmitHeader(TRUE);
    }
    if ($options['qf']) {
      $query_object->addParam('qf', $options['qf']);
    }

    $query_object->addParam('TZ', 'UTC');

    $result = $this->solr->execute($query_object);

    if ($result instanceof ResultInterface) {
      $this->logger->notice('Query executed successfully.');
      $this->logger->notice('Query result:');
      return json_encode($result->getData(), JSON_PRETTY_PRINT);
    }
    else {
      $this->logger->error('Query failed.');
      $this->logger->error('Query result:');
      return json_encode($result->getData(), JSON_PRETTY_PRINT);
    }
  }

  /**
   * Force Solr server cleanup if hash has changed.
   *
   * @usage search-api-pantheon:force-cleanup <server_id>
   *   Force server cleanup by updating hash and running a delete query on given server.
   *
   * @command search-api-pantheon:force-cleanup
   *
   * @aliases sapfc
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \Exception
   */
  public function forceServerClean($server_id = 'pantheon_solr8') {

    $server = Server::load($server_id);
    $backend = $server->getBackend();
    $connector = $backend->getSolrConnector();

    $properties['status'] = TRUE;
    $properties['read_only'] = FALSE;
    foreach ($server->getIndexes($properties) as $index) {
      // We are sure this server is only available to this env so it is safe
      // to delete all of the server contents.
      $query = '*:*';

      $update_query = $connector->getUpdateQuery();
      $update_query->addDeleteQuery($query);
      $connector->update($update_query, $backend->getCollectionEndpoint($index));
      \Drupal::state()->set('search_api_solr.' . $index->id() . '.last_update', \Drupal::time()->getCurrentTime());
    }

  }

}
