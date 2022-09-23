<?php

namespace Drupal\Tests\flood_control\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests that the Flood control UI pages are reachable.
 *
 * @group flood_control
 */
class FloodControlUiPageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['flood_control', 'contact'];

  /**
   * The admin user that can access the admin page.
   *
   * @var string
   */
  private $adminUser;

  /**
   * A simple user that cannot access the admin page.
   *
   * @var string
   */
  private $simpleUser;

  /**
   * Create required user and other objects in order to run tests.
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['access flood unblock']);
    $this->superAdminUser = $this->drupalCreateUser(['administer site configuration']);
    $this->simpleUser = $this->drupalCreateUser();

    // Flood backends need a request object. Create a dummy one and insert it
    // to the container.
    $request = Request::createFromGlobals();
    $this->container->get('request_stack')->push($request);
  }

  /**
   * Test flood control with admin user.
   */
  public function testFloodUnblockUiPageAdminUser() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/people/flood-unblock');
    $this->assertSession()->statusCodeEquals(200, 'Status code is equal to 200');

    // Test that there is an empty flood list.
    $this->assertSession()
      ->pageTextContains('There are no failed logins at this time.');
  }

  /**
   * Test flood control with simple user.
   */
  public function testFloodUnblockUiPageSimpleUser() {
    $this->drupalLogin($this->simpleUser);

    $this->drupalGet('admin/people/flood-unblock');
    $this->assertSession()->statusCodeEquals(403, 'Status code is equal to 403');
  }

  /**
   * Test flood control with admin user.
   */
  public function testFloodControlSettingsFormSuperAdminUser() {
    $this->drupalLogin($this->superAdminUser);
    $this->drupalGet('admin/config/people/flood-control');
    $this->assertSession()->statusCodeEquals(200, 'Status code is equal to 200');
  }

  /**
   * Test flood control with simple user.
   */
  public function testFloodControlSimpleUser() {
    $this->drupalLogin($this->simpleUser);
    $this->drupalGet('admin/config/people/flood-control');
    $this->assertSession()->statusCodeEquals(403, 'Status code is equal to 403');
  }

}
