<?php

namespace Drupal\search_api_pantheon\Commands;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\search_api_pantheon\Services\Endpoint;
use Drupal\search_api_pantheon\Services\PantheonGuzzle;
use Drupal\search_api_pantheon\Services\SolariumClient;
use Drupal\search_api_solr\SolrConnectorInterface;
use Drush\Commands\DrushCommands;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\QueryType\Update\Query\Document as UpdateDocument;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;
use Symfony\Component\Yaml\Yaml;

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
class Diagnose extends DrushCommands {

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
   * Search_api_pantheon:diagnose.
   *
   * @usage search-api-pantheon:diagnose
   *   Connect to the solr8 server.
   *
   * @command search-api-pantheon:diagnose
   * @aliases sapd
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \JsonException
   * @throws \Exception
   */
  public function diagnose() {
    try {
      $drupal_root = \DRUPAL_ROOT;
      $pantheon_yml_contents = '';
      if (file_exists($drupal_root . '/pantheon.yml')) {
        $pantheon_yml_contents = file_get_contents($drupal_root . '/pantheon.yml');
      }
      elseif (file_exists($drupal_root . '/../pantheon.yml')) {
        $pantheon_yml_contents = file_get_contents($drupal_root . '/../pantheon.yml');
      }
      $pantheon_upstream_yml_contents = '';
      if (file_exists($drupal_root . '/pantheon.upstream.yml')) {
        $pantheon_upstream_yml_contents = file_get_contents($drupal_root . '/pantheon.upstream.yml');
      }
      elseif (file_exists($drupal_root . '/../pantheon.upstream.yml')) {
        $pantheon_upstream_yml_contents = file_get_contents($drupal_root . '/../pantheon.upstream.yml');
      }
      if (!$pantheon_yml_contents && !$pantheon_upstream_yml_contents) {
        throw new \Exception('Unable to find pantheon.yml or pantheon.upstream.yml');
      }
      $pantheon_yml = Yaml::parse($pantheon_yml_contents);
      $pantheon_upstream_yml = Yaml::parse($pantheon_upstream_yml_contents);
      $found = FALSE;
      if (empty($pantheon_yml['search']['version'])) {
        // Merge from pantheon_upstream as fallback.
        if (!empty($pantheon_upstream_yml['search']['version'])) {
          $pantheon_yml['search']['version'] = $pantheon_upstream_yml['search']['version'];
        }
      }
      if (empty($pantheon_yml['search']['version'])) {
        // If still empty, throw an exception.
        throw new \Exception('Unable to find search.version in pantheon.yml or pantheon.upstream.yml');
      }

      if ($pantheon_yml['search']['version'] != '8') {
        throw new \Exception('Unsupported search.version in pantheon.yml or pantheon.upstream.yml');
      }
      $this->logger()->notice('Pantheon.yml file looks ok ✅');

      $this->logger()->notice('Index SCHEME Value: {var}', [
            'var' => $this->endpoint->getScheme(),
        ]);
      $this->logger()->notice('Index HOST Value:   {var}', [
            'var' => $this->endpoint->getHost(),
        ]);
      $this->logger()->notice('Index PORT Value:   {var}', [
            'var' => $this->endpoint->getPort(),
        ]);
      $this->logger()->notice('Index CORE Value:   {var}', [
            'var' => $this->endpoint->getCore(),
        ]);
      $this->logger()->notice('Index PATH Value:   {var}', [
            'var' => $this->endpoint->getPath(),
        ]);
      $this->logger()->notice('Testing bare Connection...');
      $response = $this->pingSolrHost();
      $this->logger()->notice('Ping Received Response? {var}', [
            'var' => $response instanceof ResultInterface ? '✅' : '❌',
        ]);
      $this->logger()->notice('Response http status == 200? {var}', [
            'var' => $response->getResponse()->getStatusCode() === 200 ? '✅' : '❌',
        ]);
      if ($response->getResponse()->getStatusCode() !== 200) {
        throw new \Exception('Cannot contact solr server.');
      }
      $this->logger()->notice('Drupal Integration...');
            // @codingStandardsIgnoreLine
            $manager = \Drupal::getContainer()->get(
            'plugin.manager.search_api_solr.connector'
        );
      $connectors = array_keys($manager->getDefinitions() ?? []);
      $this->logger()->notice('Pantheon Connector Plugin Exists? {var}', [
            'var' => in_array('pantheon', $connectors) ? '✅' : '❌',
        ]);
      $connectorPlugin = $manager->createInstance('pantheon');
      $this->logger()->notice('Connector Plugin Instance created {var}', [
            'var' => $connectorPlugin instanceof SolrConnectorInterface ? '✅' : '❌',
        ]);
      if (!$connectorPlugin instanceof SolrConnectorInterface) {
        throw new \Exception('Cannot instantiate solr connector.');
      }
      $this->logger()->notice('Adding Logger to connector...');
      $connectorPlugin->setLogger($this->logger);
      $this->logger()->notice('Using connector plugin to get server Info...');
      $info = $connectorPlugin->getServerInfo();
      if ($this->output()->isVerbose()) {
        $this->logger()->notice(print_r($info, TRUE));
      }
      $this->logger()->notice('Solr Server Version {var}', [
            'var' => $info['lucene']['solr-spec-version'] ?? '❌',
        ]);
      $indexSingleItemQuery = $this->indexSingleItem();
      $this->logger()->notice('Solr Update index with one document Response: {code} {reason}', [
            'code' => $indexSingleItemQuery->getResponse()->getStatusCode(),
            'reason' => $indexSingleItemQuery->getResponse()->getStatusMessage(),
        ]);
      if ($indexSingleItemQuery->getResponse()->getStatusCode() !== 200) {
        throw new \Exception('Cannot unable to index simple item. Have you created an index for the server?');
      }

      $indexedStats = $this->pantheonGuzzle->getQueryResult('admin/luke', [
            'query' => [
                'stats' => 'true',
            ],
        ]);
      if ($this->output()->isVerbose()) {
        $this->logger()->notice('Solr Index Stats: {stats}', [
              'stats' => print_r($indexedStats['index'], TRUE),
          ]);
      }
      else {
        $this->logger()->notice('We got Solr stats ✅');
      }
      $beans = $this->pantheonGuzzle->getQueryResult('admin/mbeans', [
            'query' => [
                'stats' => 'true',
            ],
        ]);
      if ($this->output()->isVerbose()) {
        $this->logger()->notice('Mbeans Stats: {stats}', [
              'stats' => print_r($beans['solr-mbeans'], TRUE),
          ]);
      }
      else {
        $this->logger()->notice('We got Mbeans stats ✅');
      }
    }
    catch (\Exception $e) {
      \Kint::dump($e);
      $this->logger->emergency("There's a problem somewhere...");
      exit(1);
    }
    catch (\Throwable $t) {
      \Kint::dump($t);
      $this->logger->emergency("There's a problem somewhere...");
      exit(1);
    }
    $this->logger()->notice(
          "If there's an issue with the connection, it would have shown up here. You should be good to go!"
      );
  }

  /**
   * Pings the Solr host.
   *
   * @usage search-api-pantheon:ping
   *   Ping the solr server.
   *
   * @command search-api-pantheon:ping
   * @aliases sapp
   *
   * @return \Solarium\Core\Query\Result\ResultInterface|\Solarium\QueryType\Ping\Result|void
   *   The result.
   */
  public function pingSolrHost() {
    try {
      $ping = $this->solr->createPing();
      return $this->solr->ping($ping);
    }
    catch (\Exception $e) {
      exit($e->getMessage());
    }
    catch (\Throwable $t) {
      exit($t->getMessage());
    }
  }

  /**
   * Indexes a single item.
   *
   * @return \Solarium\Core\Query\Result\ResultInterface|\Solarium\QueryType\Update\Result
   *   The result.
   */
  protected function indexSingleItem() {
    // Create a new document.
    $document = new UpdateDocument();

    // Set a field value as property.
    $document->id = 15;

    // Set a field value as array entry.
    $document['population'] = 120000;

    // Set a field value with the setField method, including a boost.
    $document->setField('name', 'example doc', 3);

    // Add two values to a multivalue field.
    $document->addField('countries', 'NL');
    $document->addField('countries', 'UK');
    $document->addField('countries', 'US');

    // example: add / remove field with methods.
    $document->setField('dummy', 10);
    $document->removeField('dummy');

    // example: add / remove field with methods by setting NULL value.
    $document->setField('dummy', 10);
    // This removes the field.
    $document->setField('dummy', NULL);

    // Set a document boost value.
    $document->setFieldBoost('name', 2.5);

    // Set a field boost.
    $document->setFieldBoost('population', 4.5);

    // Add it to the update query and also add a commit.
    $query = new UpdateQuery();
    $query->addDocument($document);
    $query->addCommit();
    // Run it, the result should be a new document in the Solr index.
    return $this->solr->update($query);
  }

}
