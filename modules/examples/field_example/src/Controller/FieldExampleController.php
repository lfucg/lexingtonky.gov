<?php

namespace Drupal\field_example\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for dblog routes.
 */
class FieldExampleController extends ControllerBase {

  /**
   * A simple page to explain to the developer what to do.
   */
  public function description() {
    return array(
      '#markup' => t(
        "The Field Example provides a field composed of an HTML RGB value, like #ff00ff. To use it, add the field to a content type."),
    );
  }

}
