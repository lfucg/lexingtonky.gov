<?php

namespace Drupal\field_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the user-facing menus in Field Example.
 *
 * @group field_example
 * @group examples
 *
 * @ingroup field_example
 */
class FieldExampleMenuTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field_example');

  /**
   * The installation profile to use with this test.
   *
   * This test class requires the "Tools" block.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Test for a link to the block example in the Tools menu.
   */
  public function testFieldExampleLink() {
    $this->drupalGet('');
    $this->assertLinkByHref('examples/field-example');
  }

  /**
   * Tests field_example menus.
   */
  public function testBlockExampleMenu() {
    $this->drupalGet('examples/field-example');
    $this->assertResponse(200, 'Description page exists.');
  }

}
