<?php

/**
 * @file
 * Hooks and documentation related to dropzonejs_eb_widget module.
 */

use Drupal\file\Entity\File;

/**
 * Alter media entity values before creation in eb widget.
 *
 * @param array $entity_values
 *   Entity values for current media entity.
 * @param \Drupal\file\Entity\File $file
 *   File entity used as source for the media entity.
 */
function hook_dropzonejs_eb_media_entity_prepare_alter(array &$entity_values, File $file) {
  if ($file->getMimeType() === 'image/gif') {
    $entity_values['bundle'] = 'gif';
  }
}
