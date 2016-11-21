<?php

namespace Drupal\Tests\examples\Functional;

/**
 * Minimal test case for the examples module.
 *
 * @group examples
 *
 * @ingroup examples
 */
class ExamplesTest extends ExamplesBrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('examples', 'toolbar');

  /**
   * Test whether the module was installed.
   */
  public function testExamples() {
    $assert = $this->assertSession();

    // Verify that the toolbar tab and tray are showing and functioning.
    $user = $this->drupalCreateUser(array('access toolbar'));
    $this->drupalLogin($user);

    // Check for the 'Examples' tab.
    $this->drupalGet('');
    $assert->statusCodeEquals(200);

    // Assert that the toolbar tab registered by examples is present.
    $assert->linkExists('Examples');

    // Assert that the toolbar tab registered by examples is present.
    $this->assertEquals(
      1,
      \count($this->xpath('//nav/div/a[@data-toolbar-tray="toolbar-item-examples-tray"]')),
      'Found the Examples toolbar tab.'
    );

    // Assert that the toolbar tray registered by examples is present.
    $this->assertEquals(
      1,
      \count($this->xpath('//nav/div/div[@data-toolbar-tray="toolbar-item-examples-tray"]')),
      'Found the Examples toolbar tray.'
    );
    // Assert that PHPUnit link does not appears in the tray.
    $assert->linkNotExists('PHPUnit example');
    $assert->pageTextNotContains('<li class="phpunit-example">');

    // Install phpunit_example and see if it appears in the toolbar. We use
    // phpunit_example because it's very light-weight.
    $this->container->get('module_installer')->install(array('phpunit_example'), TRUE);
    // SimpleTest needs for us to reset all the caches.
    $this->resetAll();

    // Verify that PHPUnit appears in the tray.
    $this->drupalGet('');
    $assert->linkExists('PHPUnit example');
    // Assert that the PHPUnit tray item is present.
    $this->assertEquals(
      1,
      \count($this->xpath('//nav/div/div/nav/ul/li[@class="phpunit-example"]')),
      'Found the PHPUnit Example tray item.'
    );

  }

}
