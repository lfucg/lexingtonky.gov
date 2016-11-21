<?php

namespace Drupal\simpletest_example\Controller;

/**
 * Controller for PHPUnit description page.
 */
class SimpleTestExampleController {

  /**
   * Displays a page with a descriptive page.
   *
   * Our router maps this method to the path 'examples/simpletest_example'.
   */
  public function description() {
    $build = array(
      '#markup' => t('This Simpletest Example is designed to give an introductory tutorial to writing
    a simpletest test. Please see the <a href="http://drupal.org/node/890654">associated tutorial</a>.'),
    );
    return $build;
  }

}
