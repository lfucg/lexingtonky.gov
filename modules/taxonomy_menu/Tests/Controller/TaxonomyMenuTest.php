<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\Tests\TaxonomyMenu.
 */

namespace Drupal\taxonomy_menu\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the taxonomy_menu module.
 */
class TaxonomyMenuTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "taxonomy_menu TaxonomyMenu's controller functionality",
      'description' => 'Test Unit for module taxonomy_menu and controller TaxonomyMenu.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests taxonomy_menu functionality.
   */
  public function testTaxonomyMenu() {
    // Check that the basic functions of module taxonomy_menu.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
