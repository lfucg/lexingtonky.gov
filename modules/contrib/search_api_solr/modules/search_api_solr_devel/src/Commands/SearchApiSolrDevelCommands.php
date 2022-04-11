<?php

namespace Drupal\search_api_solr_devel\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\search_api\SearchApiException;
use Drupal\search_api_solr\SearchApiSolrException;
use Drupal\search_api_solr\SolrBackendInterface;
use Drupal\search_api_solr\Utility\SolrCommandHelper;
use Drush\Commands\DrushCommands;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines Drush commands for the Search API Solr Devel.
 */
class SearchApiSolrDevelCommands extends DrushCommands {

  /**
   * The command helper.
   *
   * @var \Drupal\search_api_solr\Utility\SolrCommandHelper
   */
  protected $commandHelper;

  /**
   * Constructs a SearchApiSolrCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, EventDispatcherInterface $eventDispatcher) {
    parent::__construct();
    $this->commandHelper = new SolrCommandHelper($entityTypeManager, $moduleHandler, $eventDispatcher, 'dt');
  }

  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    parent::setLogger($logger);
    $this->commandHelper->setLogger($logger);
  }

  /**
   * Deletes *all* documents on a Solr search server (including all indexes).
   *
   * @param string $server_id
   *   The ID of the server.
   *
   * @command search-api-solr:devel-delete-all
   *
   * @usage search-api-solr-devel:delete-all server_id
   *   Deletes *all* documents on server_id.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \Drupal\search_api\SearchApiException
   */
  public function deleteAll($server_id) {
    $servers = $this->commandHelper->loadServers([$server_id]);
    if ($server = reset($servers)) {
      $backend = $server->getBackend();
      if ($backend instanceof SolrBackendInterface) {
        $connector = $backend->getSolrConnector();
        $update_query = $connector->getUpdateQuery();
        $update_query->addDeleteQuery('*:*');
        $connector->update($update_query);

        foreach ($server->getIndexes() as $index) {
          if ($index->status() && !$index->isReadOnly()) {
            if ($connector->isCloud()) {
              $connector->update($update_query, $backend->getCollectionEndpoint($index));
            }
            $index->reindex();
          }
        }
      }
      else {
        throw new SearchApiSolrException("The given server ID doesn't use the Solr backend.");
      }
    }
    else {
      throw new SearchApiException("The given server ID doesn't exist.");
    }
  }

}
