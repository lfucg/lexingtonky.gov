<?php

namespace Drupal\apax_search_algolia\Plugin\Block;

/**
 * Contains \Drupal\apax_search_algolia\Plugin\Block\AutocompleteInput.
 */

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Autocomplete Search' block for the main header area.
 *
 * @Block(
 *  id = "autocomplete_search_block",
 *  admin_label = @Translation("Autocomplete Search Block"),
 * )
 */
class AutocompleteInput extends BlockBase {

  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\block\BlockViewBuilder
   */
  public function build() {
    $build = [];

    $build[] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'autocompleteSearch',
      ],
      '#attached' => [
        'library' => [
          'apax_search_algolia/common',
          'apax_search_algolia/autocomplete',
        ],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
