<?php

namespace Drupal\apax_search_algolia\Plugin\Block;

/**
 * Contains \Drupal\apax_search_algolia\Plugin\Block\SearchResultsBlock.
*/

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Search Results' block.
 *
 * @Block(
 *  id = "search_results",
 *  admin_label = @Translation("Search Results Block"),
 * )
 */
class SearchResultsBlock extends BlockBase {

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
        'id' => 'root',
      ],
      '#attached' => [
        'library' => [
          'apax_search_algolia/common',
          'apax_search_algolia/index',
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
