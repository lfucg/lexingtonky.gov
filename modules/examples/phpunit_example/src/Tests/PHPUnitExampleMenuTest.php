<?php

namespace Drupal\phpunit_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the user-facing menus in PHPUnit Example.
 *
 * Note that this is _not_ a PHPUnit-based test. It's a functional
 * test of whether this module can be enabled properly.
 *
 * @ingroup phpunit_example
 *
 * @group phpunit_example
 * @group examples
 */
class PHPUnitExampleMenuTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('phpunit_example');

  /**
   * The installation profile to use with this test.
   *
   * We need the 'minimal' profile in order to make sure the Tool block is
   * available.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'PHPUnit Example Menu Test',
      'description' => 'Test the user-facing menus in PHPUnit Example.',
      'group' => 'Examples',
    );
  }

  /**
   * Data provider for testing menu links.
   *
   * @return array
   *   Array of page -> link relationships to check for.
   *   The key is the path to the page where our link should appear.
   *   The value is the link that should appear on that page.
   */
  protected function providerMenuLinks() {
    return array(
      '' => '/examples/phpunit-example',
    );
  }

  /**
   * Verify and validate that default menu links were loaded for this module.
   */
  public function testPHPUnitExampleLink() {
    $links = $this->providerMenuLinks();
    foreach ($links as $page => $path) {
      $this->drupalGet($page);
      $this->assertLinkByHref($path);
    }
  }

  /**
   * Tests phpunit_example menus.
   */
  public function testPHPUnitExampleMenu() {
    $this->drupalGet('/examples/phpunit-example');
    $this->assertResponse(200, 'Description page exists.');
  }

}
