<?php

namespace Drupal\search_api_solr_admin\Utility;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\search_api_solr\SearchApiSolrException;
use Drupal\search_api_solr\Utility\SolrCommandHelper;
use Drupal\search_api_solr\Utility\Utility;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides functionality to be used by CLI tools.
 */
class SolrAdminCommandHelper extends SolrCommandHelper {

  /**
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a CommandHelper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param string|callable $translation_function
   *   (optional) A callable for translating strings.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the "search_api_index" or "search_api_server" entity types'
   *   storage handlers couldn't be loaded.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the "search_api_index" or "search_api_server" entity types are
   *   unknown.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, EventDispatcherInterface $event_dispatcher, FileSystemInterface $fileSystem, MessengerInterface $messenger) {
    parent::__construct($entity_type_manager, $module_handler, $event_dispatcher);
    $this->fileSystem = $fileSystem;
    $this->messenger = $messenger;
  }

  /**
   * Reload Solr core or collection.
   *
   * @param string $server_id
   *   The ID of the server.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function reload(string $server_id): void {
    $server = $this->getServer($server_id);
    $connector = Utility::getSolrConnector($server);
    $result = $connector->reloadCore();
    if (!$result) {
      throw new SearchApiSolrException(sprintf('Reloading %s for %s (%s) failed.', $connector->isCloud() ? 'collection' : 'core', $server->label(), $server_id));
    }
    $this->reindex($server);
  }

  /**
   * Delete Solr collection.
   *
   * @param string $server_id
   *   The ID of the server.
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function deleteCollection(string $server_id): void {
    $server = $this->getServer($server_id);
    $connector = Utility::getSolrCloudConnector($server);
    $result = $connector->deleteCollection();
    if (!$result) {
      throw new SearchApiSolrException(sprintf('Reloading %s for %s (%s) failed.', $connector->isCloud() ? 'collection' : 'core', $server->label(), $server_id));
    }
    $this->reindex($server);
  }

  /**
   * Generates and uploads the configset for a Solr search server.
   *
   * @param string $server_id
   *   The ID of the server.
   * @param int $num_shards
   * @param bool $messages
   *
   * @throws \Drupal\search_api\SearchApiException
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   * @throws \ZipStream\Exception\FileNotFoundException
   * @throws \ZipStream\Exception\FileNotReadableException
   * @throws \ZipStream\Exception\OverflowException
   */
  public function uploadConfigset(string $server_id, int $num_shards = 3, bool $messages = FALSE): void {
    $server = $this->getServer($server_id);
    $connector = Utility::getSolrCloudConnector($server);

    if ($messages) {
      // Called via admin form. 'dt' is not available.
      $this->translationFunction = 't';
    }

    $filename = $this->fileSystem->tempnam($this->fileSystem->getTempDirectory(), 'configset_') . '.zip';
    $this->getServerConfigCommand($server->id(), $filename);

    $configset = $connector->getConfigSetName();
    $collection_exists = (bool) $configset;
    if (!$collection_exists) {
      $configset = Utility::generateConfigsetName($server);
    }

    $connector->uploadConfigset($configset, $filename);
    if ($messages) {
      $this->messenger->addStatus($this->t('Successfully uploaded configset %configset.', ['%configset' => $configset]));
    }

    if ($collection_exists) {
      $this->reload($server_id);
      if ($messages) {
        $this->messenger->addStatus($this->t('Successfully reloaded collection %collection.', ['%collection' => $connector->getCollectionName()]));
      }
    }
    else {
      $connector->createCollection([
        'collection.configName' => $configset,
        'numShards' => $num_shards
      ]);
      if ($messages) {
        $this->messenger->addStatus($this->t('Successfully created collection %collection.', ['%collection' => $connector->getCollectionName()]));
      }
      $this->reindex($server);
    }
  }

}
