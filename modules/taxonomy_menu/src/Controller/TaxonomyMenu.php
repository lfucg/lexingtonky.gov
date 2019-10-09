<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\Controller\TaxonomyMenu.
 */

namespace Drupal\taxonomy_menu\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class TaxonomyMenu.
 *
 * @package Drupal\taxonomy_menu\Controller
 */
class TaxonomyMenu extends ControllerBase {

 // TODO - REMOVE THIS

  /**
   * Render taxonomy links.
   *
   * @return string
   *   Return Hello string.
   */
  public function renderTaxonomyLinks() {

    $markup = '';

    /*
    // Check current main menu.
    $menu_tree = \Drupal::menuTree();
    $parameters = new MenuTreeParameters();
    $tree = $menu_tree->load('main', $parameters);
    $markup .= var_export($tree, TRUE);
    */

    // Load taxonomy menus.
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_menu');
    $taxonomy_menus = $storage->loadMultiple();
    $links = [];

    // Get taxonomy and create menu links from vocabularies.
    foreach ($taxonomy_menus as $taxonomy_menu) {
      $links += $taxonomy_menu->generateTaxonomyLinks([]);
    }

    //$markup .= var_export($links, TRUE);

    return [
        '#type' => 'markup',
        '#markup' => $this->t($markup),
    ];
  }

}
