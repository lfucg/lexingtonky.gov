<?php

namespace Drupal\contentimport\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for contentimport routes.
 */
class ContentImportController extends ControllerBase {

  /**
   * Get All Content types.
   */
  public static function getAllContentTypes() {
    $contentTypes = \Drupal::service('entity_type.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    $contentTypesList['none'] = 'Select';
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    return $contentTypesList;
  }

}
