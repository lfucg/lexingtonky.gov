<?php

namespace Drupal\taxonomy_menu;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Provides an interface defining a TaxonomyMenu entity.
 */
interface TaxonomyMenuInterface extends ConfigEntityInterface {

  /**
   * Get the menu that the menu items are being created in.
   *
   * @return string
   *   The machine name of the menu entity holding the vocabulary's menu items.
   */
  public function getMenu();

  /**
   * Get the vocabulary being used.
   *
   * @return string
   *   The vocabulary whose terms will be used to generate a menu.
   */
  public function getVocabulary();

  /**
   * Get the depth of terms to generate menu items for.
   *
   * @return int
   *   The depth.
   */
  public function getDepth();

  /**
   * Get the menu item to use as the parent for the taxonomy menu.
   *
   * @return string
   *   The menu item id string.
   */
  public function getMenuParent();

  /**
   * @return string
   *   The machine name of the field to be used as the description.
   */
  public function getDescriptionFieldName();

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
   *   Associative array of menu links ids and definitions.
   */
  public function getLinks($base_plugin_definition = [], $include_base_plugin_id = FALSE);

  /**
   * Generates a menu link id for the taxonomy term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term to build menu item for.
   * @param bool $include_base_plugin_id
   *   Include base plugin id in menu item id.
   * @return string
   *    A unique string id for the menu item.
   */
  public function buildMenuPluginId(TermInterface $term, $include_base_plugin_id = TRUE);

}
