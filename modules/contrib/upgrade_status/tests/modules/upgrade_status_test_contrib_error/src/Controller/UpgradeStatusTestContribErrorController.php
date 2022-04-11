<?php

namespace Drupal\upgrade_status_test_contrib_error\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Test class which contains deprecation error.
 */
class UpgradeStatusTestContribErrorController extends ControllerBase {

  public function content() {
    upgrade_status_test_contrib_error_function_8_to_9();
    upgrade_status_test_contrib_error_function_8_to_10();
    upgrade_status_test_contrib_error_function_9_to_10();
    upgrade_status_test_contrib_error_function_9_to_11();
  }

}
