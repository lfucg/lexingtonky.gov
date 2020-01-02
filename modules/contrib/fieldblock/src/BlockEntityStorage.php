<?php

namespace Drupal\fieldblock;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entityTypeManager) {
    $this->configFactory = $config_factory;
    $this->uuidService = $uuid_service;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entityTypeManager;
    $entity_type = $entityTypeManager->getDefinition('block');
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager);
  }

  /**
   * Load all blocks provided by this module.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Block entities.
   */
  public function loadFieldBlocks() {
    // Build a query to fetch the entity IDs.
    $entity_query = $this->getQuery();
    $entity_query->condition('plugin', 'fieldblock:', 'STARTS_WITH');
    $result = $entity_query->execute();
    return $result ? $this->loadMultiple($result) : [];
  }

  /**
   * Get all entity type ids that are currently used in Field Blocks.
   *
   * This will also return entity type ids for entities that are no longer
   * available.
   *
   * @return array
   *   Entity type machine names.
   */
  public function getEntityTypesUsed() {
    $blocks = $this->loadFieldBlocks();
    $entity_types = [];
    /** @var \Drupal\block\Entity\Block $block */
    foreach ($blocks as $block) {
      $plugin_parts = explode(':', $block->get('plugin'));
      $entity_types[] = $plugin_parts['1'];
    }

    return $entity_types;
  }

  /**
   * Delete all blocks for an entity type.
   *
   * @param string $entity_type
   *   The entity type.
   */
  public function deleteBlocksForEntityType($entity_type) {
    $blocks = $this->loadByProperties(['plugin' => "fieldblock:$entity_type"]);
    $this->delete($blocks);
  }

}
