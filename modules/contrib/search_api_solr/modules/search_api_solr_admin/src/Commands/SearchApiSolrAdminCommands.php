<?php

namespace Drupal\search_api_solr_admin\Commands;

use Consolidation\AnnotatedCommand\Input\StdinAwareInterface;
use Consolidation\AnnotatedCommand\Input\StdinAwareTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\search_api\ConsoleException;
use Drupal\search_api_solr\SearchApiSolrException;
use Drupal\search_api_solr\SolrBackendInterface;
use Drupal\search_api_solr\SolrCloudConnectorInterface;
use Drupal\search_api_solr\Utility\SolrCommandHelper;
use Drupal\search_api_solr_admin\Utility\SolrAdminCommandHelper;
use Drush\Commands\DrushCommands;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines Drush commands for the Search API Solr Admin.
 */
class SearchApiSolrAdminCommands extends DrushCommands implements StdinAwareInterface {

  use StdinAwareTrait;

  /**
   * The command helper.
   *
   * @var \Drupal\search_api_solr_admin\Utility\SolrAdminCommandHelper
   */
  protected $commandHelper;

  /**
   * Constructs a SearchApiSolrCommands object.
   *
   * @param \Drupal\search_api_solr_admin\Utility\SolrAdminCommandHelper $commandHelper
   *   The command helper.
   */
  public function __construct(SolrAdminCommandHelper $commandHelper) {
    parent::__construct();
    $this->commandHelper = $commandHelper;
  }

  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    parent::setLogger($logger);
    $this->commandHelper->setLogger($logger);
  }

  /**
   * Reload Solr core or collection.
   *
   * @param string $server_id
   *   The ID of the server.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   *
   * @command search-api-solr:reload
   *
   * @usage drush search-api-solr:reload server_id
   *   Forces the Solr server to reload the core or collection to apply config changes.
   *
   * @aliases solr-reload
   */
  public function reload(string $server_id): void {
    $this->commandHelper->reload($server_id);
    $this->logger()->success(dt('Solr core/collection of %server_id reloaded.', ['%server_id' => $server_id]));
  }

  /**
   * Delete Solr collection.
   *
   * @param string $server_id
   *   The ID of the server.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   *
   * @command search-api-solr:delete-collection
   *
   * @usage drush search-api-solr:delete-collection server_id
   *   Forces the Solr server to delete the collection.
   *
   * @aliases solr-delete-collection
   */
  public function deleteCollection(string $server_id): void {
    $this->commandHelper->deleteCollection($server_id);
    $this->logger()->success(dt('Solr collection of %server_id deleted.', ['%server_id' => $server_id]));
  }

  /**
   * Upload a configset and reload the collection or create it.
   *
   * @param string $server_id
   *   The ID of the server.
   * @param string $num_shards
   *   (optional) The number of shards in case a new collection will be created.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \ZipStream\Exception\FileNotFoundException
   * @throws \ZipStream\Exception\FileNotReadableException
   * @throws \ZipStream\Exception\OverflowException
   *
   * @command search-api-solr:upload-configset
   *
   * @usage drush search-api-solr:upload-configset server_id num_shards
   *   Upload a configset and reload the collection or create it with num_shards.
   *
   * @aliases solr-upload-conf
   */
  public function uploadConfigset(string $server_id, int $num_shards = 3): void {
    $this->commandHelper->uploadConfigset($server_id, $num_shards, $this->output()->isVerbose());
    $this->logger()->success(dt('Solr configset for %server_id uploaded.', ['%server_id' => $server_id]));
  }

}
