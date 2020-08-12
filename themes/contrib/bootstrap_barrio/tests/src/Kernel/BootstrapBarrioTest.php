<?php

namespace Drupal\Tests\bootstrap_barrio\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Bootstrap Barrio Tests.
 *
 * @group bootstrap_barrio
 */
class BootstrapBarrioTest extends KernelTestBase {

  /**
   * Dummy test to run DrupalCI. Only to check if issue queue patches apply.
   */
  public function testBootstrapBarrioCore() {
    $this->assertTrue(TRUE, 'Assert TRUE.');
    $this->assertFalse(FALSE, 'Assert FALSE.');
  }

}
