<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\TaxonomyMenuInterface.
 */

namespace Drupal\taxonomy_menu;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Provides an interface defining a TaxonomyMenu entity.
 */
interface TaxonomyMenuInterface extends ConfigEntityInterface {
  
  /**
   * @return string
   *   The machine name of the menu entity which hold the vocabulary's menu items.
   */
  public function getMenu();

  /**
   * @return string
   *   The vocabulary whose terms will be used to generate a menu.
   */
  public function getVocabulary();

  /**
   * @todo
   */
  public function getDepth();

  /**
   * @todo
   */
  public function getMenuParent();

  /**
   * Get menu link plugin definitions
   *
   * @param array $base_plugin_definition
   *
   * @param bool $include_base_plugin_id
   *   If true, 'taxonomy_menu.menu_link:' will be prepended to the returned
   *   plugin ids.
   *
   * @return array
   */
  public function getLinks($base_plugin_definition = [], $include_base_plugin_id = FALSE);

  /**
   * Generates a menu link id for the taxonomy term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *
   * @return string
   */
  public function buildMenuPluginId(TermInterface $term, $include_base_plugin_id = TRUE);

}
