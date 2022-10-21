<?php

namespace Drupal\pantheon_advanced_page_cache;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Cache tags invalidator implementation that invalidates the Pantheon edge.
 */
class CacheTagsInvalidator implements CacheTagsInvalidatorInterface {

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    if (function_exists('pantheon_clear_edge_keys')) {
      pantheon_clear_edge_keys($tags);
    }
  }

}
