<?php

/**
 * @file
 * Post update functions for Metatag.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\metatag\Entity\MetatagDefaults;

/**
 * Convert mask-icon to array values.
 */
function metatag_post_update_convert_mask_icon_to_array_values(&$sandbox) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $config_entity_updater->update($sandbox, 'metatag_defaults', function (MetatagDefaults $metatag_defaults) {
    if ($metatag_defaults->hasTag('mask-icon')) {
      $tags = $metatag_defaults->get('tags');
      $tags['mask_icon'] = [
        'href' => $metatag_defaults->getTag('mask-icon'),
      ];
      unset($tags['mask-icon']);
      $metatag_defaults->set('tags', $tags);
      return TRUE;
    }
    return FALSE;
  });
}
