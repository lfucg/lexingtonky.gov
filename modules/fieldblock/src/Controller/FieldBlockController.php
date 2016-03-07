<?php

/**
 * @file
 * Contains Drupal\fieldblock\Controller\FieldBlockController.
 */

namespace Drupal\fieldblock\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Class FieldBlockController.
 *
 * @package Drupal\fieldblock\Controller
 */
class FieldBlockController extends ControllerBase {
  /**
   * Get currently enabled types either from config or module defaults.
   *
   * @return array
   *  Entity type ids.
   */
  public function getEnabledEntityTypes() {
    $entity_types = $this->config('fieldblock.settings')->get('enabled_entity_types');
    if (!$entity_types) {
      return $this->getDefaultEntityTypes();
    }
    return array_filter($entity_types);
  }
  /**
   * Determine if Entity Type should have field block created.
   * @param \Drupal\Core\Entity\EntityTypeInterface $type
   *
   * @return bool
   */
  public function isBlockableEntityType(EntityTypeInterface $type) {
    static $entity_types;
    if (!$entity_types) {
      $entity_types = $this->getEnabledEntityTypes();
    }
    return in_array($type->id(), $entity_types);
  }

  /**
   * Return default entity types to use as blocks.
   *
   * @return array
   *  Entity type ids.
   */
  protected function getDefaultEntityTypes() {
    $default_types = ['node', 'user', 'taxonomy_term'];
    // @todo Should there by an alter hook to allow other modules to make their entities default?
    $all_types = array_keys($this->entityManager()->getDefinitions());
    // Return all default types that actually exist. "taxonomy_term" at least could be disabled.
    return array_intersect($default_types, $all_types);
  }

  /**
   * Determine if a Entity is compatible with Field Blocks.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *
   * @return bool
   */
  public function isFieldBlockCompatible(EntityTypeInterface $entity_type) {
    return $entity_type instanceof ContentEntityTypeInterface;
  }

}
