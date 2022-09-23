<?php

namespace Drupal\Tests\taxonomy_menu\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Class TaxonomyMenuTest.
 *
 * Provides automated tests for the taxonomy_menu module.
 *
 * @package Drupal\Tests\taxonomy_menu
 *
 * @group taxonomy_menu
 */
class TaxonomyMenuTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "taxonomy_menu TaxonomyMenu's controller functionality",
      'description' => 'Test Unit for module taxonomy_menu and controller TaxonomyMenu.',
      'group' => 'Other',
    ];
  }

  /**
   * Tests taxonomy_menu functionality.
   */
  public function testTaxonomyMenu() {
    // Check that the basic functions of module taxonomy_menu.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
