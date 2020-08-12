<?php

/**
 * @file
 * Post update functions for Search API.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;

/**
 * Re-save Search API index configurations to fix dependencies.
 */
function search_api_post_update_fix_index_dependencies(&$sandbox = NULL) {
  \Drupal::classResolver(ConfigEntityUpdater::class)
    ->update($sandbox, 'search_api_index', function () {
      // Re-save all search API indexes.
      return TRUE;
    });
}
