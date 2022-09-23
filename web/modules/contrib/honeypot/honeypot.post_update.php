<?php

/**
 * @file
 * Post update functions for Honeypot.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\tour\Entity\Tour;

/**
 * Convert deprecated Joyride 'location' properties to 'position' properties.
 */
function honeypot_post_update_joyride_location_to_position(array &$sandbox = NULL): void {
  if (\Drupal::moduleHandler()->moduleExists('tour')) {
    $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
    $config_entity_updater->update($sandbox, 'tour', function (Tour $tour) {
      return honeypot_tour_update_joyride($tour);
    });
  }
}

/**
 * Updates 'honeypot' tour to correct use of the deprecated location property.
 *
 * @param \Drupal\tour\Entity\Tour $tour
 *   The tour to update.
 *
 * @return bool
 *   Whether or not the entity needs saving.
 *
 * @see honeypot_post_update_joyride_location_to_position()
 *
 * @internal
 */
function honeypot_tour_update_joyride(Tour $tour): bool {
  $needs_save = FALSE;

  // Only change the 'honeypot' tour.
  $id = $tour->get('id');
  if ($id === 'honeypot') {
    $tips = $tour->get('tips');
    foreach ($tips as &$tip) {
      // The tour module will automatically change a 'location' property to a
      // position property in some circumstances. If the tour module does this,
      // the value of the property will have an extra '-start' appended.
      if (isset($tip['position'])) {
        $tip['position'] = str_replace('-start-start', '-start', $tip['position']);
        $needs_save = TRUE;
      }
      // If the tour module doesn't change the 'location' property, we have to
      // do it ourselves.
      if (isset($tip['location'])) {
        $needs_save = TRUE;
        $tip['position'] = $tip['location'];
        unset($tip['location']);
      }
    }

    if ($needs_save) {
      $tour->set('tips', $tips);
    }
  }

  return $needs_save;
}

/**
 * Rebuild the service container after adding new 'honeypot' service.
 */
function honeypot_post_update_rebuild_service_container(): void {
  // An empty update will flush all caches.
}
