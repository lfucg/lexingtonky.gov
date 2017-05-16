<?php

namespace Drupal\search_api_solr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\devel\DevelDumperManager;
use Drupal\search_api\Backend\BackendPluginManager;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Drupal\search_api\Utility\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for devel module routes.
 */
class DevelController extends ControllerBase {

  /**
   * The server storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The backend plugin manager.
   *
   * @var \Drupal\search_api\Backend\BackendPluginManager
   */
  protected $backendPluginManager;

  /**
   * The Devel dumper manager.
   *
   * @var \Drupal\devel\DevelDumperManager
   */
  protected $develDumperManager;

  /**
   * @var \Drupal\search_api\Utility\FieldsHelperInterface
   */
  protected $fieldsHelper;

  /**
   * Constructs a DevelController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\search_api\Backend\BackendPluginManager $backend_plugin_manager
   *   The backend plugin manager.
   * @param \Drupal\devel\DevelDumperManager $devel_dumper_manager
   *   The Devel dumper manager.
   * @param \Drupal\search_api\Utility\FieldsHelperInterface $fields_helper
   *   The Search API Fields Helper.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, BackendPluginManager $backend_plugin_manager, DevelDumperManager $devel_dumper_manager, FieldsHelperInterface $fields_helper) {
    $this->storage = $entity_type_manager->getStorage('search_api_server');
    $this->backendPluginManager = $backend_plugin_manager;
    $this->develDumperManager = $devel_dumper_manager;
    $this->fieldsHelper = $fields_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.search_api.backend'),
      $container->get('devel.dumper'),
      $container->get('search_api.fields_helper')
    );
  }

  /**
   * Retrieves the server storage controller.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The server storage controller.
   */
  protected function getStorage() {
    return $this->storage ?: \Drupal::service('entity_type.manager')->getStorage('search_api_server');
  }

  /**
   * Retrieves the backend plugin manager.
   *
   * @return \Drupal\search_api\Backend\BackendPluginManager
   *   The backend plugin manager.
   */
  protected function getBackendPluginManager() {
    return $this->backendPluginManager ?: \Drupal::service('plugin.manager.search_api.backend');
  }

  /**
   * Returns all available Solr backend plugins.
   *
   * @return string[]
   *   An associative array mapping backend plugin IDs to their (HTML-escaped)
   *   labels.
   */
  protected function getBackends() {
    $backends = array();
    $plugin_definitions = $this->getBackendPluginManager()->getDefinitions();
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      if (is_a($plugin_definition['class'], $plugin_definitions['search_api_solr']['class'], TRUE)) {
        $backends[] = $plugin_id;
      }
    }
    return $backends;
  }

  /**
   * Prints the document structure to be indexed by Solr.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A RouteMatch object.
   *
   * @return array
   *   Array of page elements to render.
   */
  public function entitySolr(RouteMatchInterface $route_match) {
    $output = [];

    $parameter_name = $route_match->getRouteObject()->getOption('_devel_entity_type_id');
    $entity = $route_match->getParameter($parameter_name);

    if ($entity && $entity instanceof EntityInterface) {
      foreach ($this->getBackends() as $backend) {
        /** @var \Drupal\search_api\ServerInterface[] $servers */
        $servers = $this->getStorage()->loadByProperties(['backend' => $backend]);
        foreach ($servers as $server) {
          /** @var SolrBackendInterface $backend */
          $backend = $server->getBackend();
          $indexes = $server->getIndexes();
          foreach ($indexes as $index) {
            if (!$index->isReadOnly()) {
              foreach ($index->getDatasourceIds() as $datasource_id) {
                list(, $entity_type) = Utility::splitPropertyPath($datasource_id);
                if ($entity->getEntityTypeId() == $entity_type) {
                  foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
                    // @todo improve that ID generation?
                    $item_id = $datasource_id . '/' . $entity->id() . ':' . $langcode;
                    $items[$item_id] = $this->fieldsHelper->createItemFromObject($index, $entity->getTranslation($langcode)->getTypedData(), $item_id);
                    // Preprocess the indexed items.
                    \Drupal::moduleHandler()->alter('search_api_index_items', $index, $items);
                    $index->preprocessIndexItems($items);

                    $documents = $backend->getDocuments($index, $items);
                    foreach ($documents as $document) {
                      $output[$server->id() . $index->id() . $item_id] = [
                        '#markup' => $this->develDumperManager->dumpOrExport($document->getFields(), $this->t('Translation %langcode to be stored in index %index on server %server', [
                          '%langcode' => $langcode,
                          '%index' => $index->label(),
                          '%server' => $server->label(),
                        ]), TRUE),
                      ];
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    return $output;
  }

}
