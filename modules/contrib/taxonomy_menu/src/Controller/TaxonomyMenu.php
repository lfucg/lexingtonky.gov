<?php

namespace Drupal\taxonomy_menu\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Render taxonomy links.
 *
 * @package Drupal\taxonomy_menu\Controller
 */
class TaxonomyMenu extends ControllerBase {

  /**
   * Render taxonomy links.
   *
   * @return string
   *   Return Hello string.
   */
  public function renderTaxonomyLinks() {
    // Load taxonomy menus.
    $taxonomy_menus = $this->entityTypeManager()
      ->getStorage('taxonomy_menu')
      ->loadMultiple();
    $links = [];

    // Get taxonomy and create menu links from vocabularies.
    foreach ($taxonomy_menus as $taxonomy_menu) {
      $links += $taxonomy_menu->getLinks([]);
    }

    return [
      '#type' => 'markup',
      '#markup' => '',
    ];
  }

}
