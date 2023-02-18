<?php

namespace Drupal\browsersync\Tests;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Browsersync functionality.
 *
 * @group browsersync
 */
class BrowsersyncTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['browsersync'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a dev user who can use Browsersync.
    $dev_user = $this->drupalCreateUser(['use browsersync']);
    $this->drupalLogin($dev_user);

    // Enable Browsersync globally.
    \Drupal::configFactory()->getEditable('system.theme.global')
      ->set('third_party_settings.browsersync.enabled', TRUE)
      ->save();
  }

  /**
   * Checks for the Browsersync snippet in the markup.
   */
  public function testSnippet() {
    $this->drupalGet('<front>');
    $elements = $this->xpath('//script[@id=:id]', [':id' => '__bs_script__']);
    $this->assertTrue(!empty($elements), 'Page contains the Browsersync snippet.');

    // Log out and check that the snippet is gone.
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $elements = $this->xpath('//script[@id=:id]', [':id' => '__bs_script__']);
    $this->assertTrue(empty($elements), 'Page does not contain the Browsersync snippet.');
  }

}
