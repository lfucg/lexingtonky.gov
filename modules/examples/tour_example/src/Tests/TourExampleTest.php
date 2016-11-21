<?php

namespace Drupal\tour_example\Tests;

use Drupal\tour\Tests\TourTestBasic;

/**
 * Regression tests for the tour_example module.
 *
 * We use TourTestBasic to get some built-in tour tip testing assertions.
 *
 * @ingroup tour_example
 *
 * @group tour_example
 * @group examples
 */
class TourExampleTest extends TourTestBasic {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tour', 'tour_example');

  /**
   * The installation profile to use with this test.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Main test.
   *
   * Enable Tour Example and see if it can successfully return its main page
   * and if there is a link to the tour example in the Tools menu.
   */
  public function testTourExample() {

    // Test for a link to the tour_example in the Tools menu.
    $this->drupalGet('');
    $this->assertResponse(200, 'The Home page is available.');
    $this->assertLinkByHref('examples/tour-example');

    // Verify if the can successfully access the tour_examples page.
    $this->drupalGet('examples/tour-example');
    $this->assertResponse(200, 'The Tour Example description page is available.');

    // Verify that the tour tips exist on this page.
    $this->assertTourTips();
  }

}
