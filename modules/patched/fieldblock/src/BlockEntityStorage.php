<?php

/**
 * @file
 * Contains Drupal\fieldblock\BlockEntityStorage.
 */

namespace Drupal\fieldblock;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class BlockEntityStorage.
 *
 * @package Drupal\fieldblock
 */
class BlockEntityStorage extends ConfigEntityStorage {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Component\Uuid\Php definition.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Drupal\Core\Language\LanguageManager definition.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Drupal\Core\Entity\EntityManager definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @throws PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, EntityManagerInterface $entity_manager) {
    $this->configFactory = $config_factory;
    $this->uuidService = $uuid_service;
    $this->languageManager = $language_manager;
    $this->entityManager = $entity_manager;
    $entity_type = $entity_manager->getDefinition('block');
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager);
  }

  /**
   * Load all blocks provided by this module.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   */
  public function loadFieldBlocks() {
    // Build a query to fetch the entity IDs.
    $entity_query = $this->getQuery();
    $entity_query->condition('plugin', 'fieldblock:', 'STARTS_WITH');
    $result = $entity_query->execute();
    return $result ? $this->loadMultiple($result) : array();
  }

  /**
   * Get all entity type ids that are currently used in Field Blocks.
   *
   * This will also return entity type ids for entities that no longer available.
   * @return array
   */
  public function getEntityTypesUsed() {
    $blocks = $this->loadFieldBlocks();
    $entity_types = [];
    /** @var \Drupal\block\Entity\Block $block */
    foreach ($blocks as $block) {
      $plugin_parts = explode(':',$block->get('plugin'));
      $entity_types[] = $plugin_parts['1'];
    }

    return $entity_types;
  }

  /**
   * Delete all blocks for an entity type.
   *
   * @param $entity_type
   */
  public function deleteBlocksForEntityType($entity_type) {
    $blocks = $this->loadByProperties(['plugin' => "fieldblock:$entity_type"]);
    $this->delete($blocks);
  }
}
