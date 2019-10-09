<?php

namespace Drupal\autologout\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the autologout's features.
 *
 * @description Ensure that the autologout module functions as expected
 *
 * @group Autologout
 */
class AutologoutTest extends WebTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'system',
    'system_test',
    'views',
    'user',
    'autologout',
    'menu_ui',
    'block',
  ];

  /**
   * Use the Standard profile to test help implementations of many core modules.
   */
  protected $profile = 'standard';

  /**
   * User with admin rights.
   */
  protected $privilegedUser;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Stores the user data service used by the test.
   *
   * @var \Drupal\user\UserDataInterface
   */
  public $userData;

  /**
   * SetUp() performs any pre-requisite tasks that need to happen.
   */
  public function setUp() {
    parent::setUp();
    // Create and log in our privileged user.
    $this->privilegedUser = $this->drupalCreateUser([
      'access content',
      'administer site configuration',
      'access site reports',
      'access administration pages',
      'bypass node access',
      'administer content types',
      'administer nodes',
      'administer autologout',
      'change own logout threshold',
      'access site reports',
      'view the administration theme',
    ]);
    $this->drupalLogin($this->privilegedUser);

    $this->configFactory = $this->container->get('config.factory');
    $this->userData = $this->container->get('user.data');

    $config = $this->configFactory->getEditable('autologout.settings');
    // For the purposes of the test, set the timeout periods to 10 seconds.
    $config->set('timeout', 10)
      ->save();

    $this->drupalLogin($this->privilegedUser);

    // Make node page default.
    $this->configFactory->getEditable('system.site')->set('page.front', 'node')->save();
    // Place the User login block on the home page to verify Log out text.
    $this->drupalPlaceBlock('system_menu_block:account');
  }

  /**
   * Test the precedence of the timeouts.
   *
   * This tests the following function:
   * _autologout_get_user_timeout();
   */
  public function testAutologoutTimeoutPrecedence() {
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    $autologout_role_settings = $this->configFactory->getEditable('autologout.role.authenticated');
    $uid = $this->privilegedUser->id();
    $autologout_user_settings = \Drupal::service('user.data');

    // Default used if no role is specified.
    $autologout_settings->set('timeout', 100)
      ->set('role_logout', FALSE)
      ->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $this->assertAutotimeout($uid, 100, 'User timeout uses default if no other option set');

    // Default used if role selected but no user's role is selected.
    $autologout_settings->set('role_logout', TRUE)
      ->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $this->assertAutotimeout($uid, 100, 'User timeout uses default if  role timeouts are used but not one of the current user.');

    // Role timeout is used if user's role is selected.
    $autologout_settings->set('role_logout', TRUE)
      ->save();
    $autologout_role_settings->set('enabled', TRUE)
      ->set('timeout', 200)
      ->save();
    $this->assertAutotimeout($uid, 200, 'User timeout uses role value');

    // Role timeout is used if user's role is selected.
    $autologout_settings->set('role_logout', TRUE)
      ->save();
    $autologout_role_settings->set('enabled', TRUE)
      ->set('timeout', 0)
      ->save();
    $this->assertAutotimeout($uid, 0, 'User timeout uses role value of 0 if set for one of the user roles.');

    // Role timeout used if personal timeout is empty string.
    $autologout_settings->set('role_logout', TRUE)
      ->save();
    $autologout_role_settings->set('enabled', TRUE)
      ->set('timeout', 200)
      ->save();
    $autologout_user_settings->set('autologout', $uid, 'timeout', '');
    $autologout_user_settings->set('autologout', $uid, 'enabled', FALSE);
    $this->assertAutotimeout($uid, 200, 'User timeout uses role value if personal value is the empty string.');

    // Default timeout used if personal timeout is empty string.
    $autologout_settings->set('role_logout', TRUE)
      ->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $autologout_user_settings->set('autologout', $uid, 'timeout', '');
    $autologout_user_settings->set('autologout', $uid, 'enabled', FALSE);
    $this->assertAutotimeout($uid, 100, 'User timeout uses default value if personal value is the empty string and no role timeout is specified.');

    // Personal timeout used if set.
    $autologout_settings->set('role_logout', TRUE)
      ->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $autologout_user_settings->set('autologout', $uid, 'timeout', 300);
    $autologout_user_settings->set('autologout', $uid, 'enabled', TRUE);
    $this->assertAutotimeout($uid, 300, 'User timeout uses default value if personal value is the empty string and no role timeout is specified.');
  }

  /**
   * Test a user is logged out after the default timeout period.
   */
  public function testAutologoutDefaultTimeout() {
    // Check that the user can access the page after login.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Homepage is accessible');
    $this->assertText('Log out', 'User is still logged in.');

    // Wait for timeout period to elapse.
    sleep(30);

    // Check we are now logged out.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Homepage is accessible');
    $this->assertNoRaw(t('Log out'), 'User is no longer logged in.');
    $this->assertText(t('You have been logged out due to inactivity.'), 'User sees inactivity message.');
  }

  /**
   * Test a user is not logged out within the default timeout period.
   */
  public function testAutologoutNoLogoutInsideTimeout() {
    // Check that the user can access the page after login.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Homepage is accessible');
    $this->assertText(t('Log out'), 'User is still logged in.');

    // Wait within the timeout period.
    sleep(10);

    // Check we are still logged in.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Homepage is accessible');
    $this->assertText(t('Log out'), 'User is still logged in.');
    $this->assertNoText(t('You have been logged out due to inactivity.'), 'User does not see inactivity message.');
  }

  /**
   * Test the behaviour of the settings for submission.
   */
  public function testAutologoutSettingsForm() {
    $edit = [];
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    $autologout_settings->set('max_timeout', 1000)
      ->save();

    $roles = user_roles(TRUE);
    // Unset authenticated, as it will be used to add manual value later.
    unset($roles['authenticated']);

    // Test that it is possible to set a value above the max_timeout
    // threshold.
    $edit['timeout'] = 1500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    $edit['table[authenticated][enabled]'] = TRUE;
    $edit['table[authenticated][timeout]'] = 1200;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = TRUE;
      $edit['table[' . $key . '][timeout]'] = 1200;
    }
    $edit['redirect_url'] = '/user/login';

    $this->drupalPostForm('admin/config/people/autologout', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'), 'Unable to save autologout config when modifying the max timeout.');

    // Test that out of range values are picked up.
    $edit['timeout'] = 2500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    $edit['table[authenticated][enabled]'] = TRUE;
    $edit['table[authenticated][timeout]'] = 1200;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = TRUE;
      $edit['table[' . $key . '][timeout]'] = 1200;
    }
    $edit['redirect_url'] = '/user/login';
    $this->drupalPostForm('admin/config/people/autologout', $edit, t('Save configuration'));
    $this->assertNoText(t('The configuration options have been saved.'), 'Saved configuration despite the autologout_timeout being too large.');

    // Test that out of range values are picked up.
    $edit['timeout'] = 1500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    $edit['table[authenticated][enabled]'] = TRUE;
    $edit['table[authenticated][timeout]'] = 2500;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = TRUE;
      $edit['table[' . $key . '][timeout]'] = 1200;
    }
    $edit['redirect_url'] = '/user/login';
    $this->drupalPostForm('admin/config/people/autologout', $edit, t('Save configuration'));
    $this->assertNoText(t('The configuration options have been saved.'), 'Saved configuration despite a role timeout being too large.');

    // Test that role timeouts are not validated for disabled roles.
    $edit['timeout'] = 1500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    $edit['table[authenticated][enabled]'] = FALSE;
    $edit['table[authenticated][timeout]'] = 4000;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = FALSE;
      $edit['table[' . $key . '][timeout]'] = 1200;
    }
    $edit['redirect_url'] = '/user/login';

    $this->drupalPostForm('admin/config/people/autologout', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'), 'Unable to save autologout due to out of range role timeout for a role which is not enabled..');
  }

  /**
   * Test a user is logged out and denied access to admin pages.
   */
  public function testAutologoutDefaultTimeoutAccessDeniedToAdmin() {
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    // Enforce auto logout of admin pages.
    $autologout_settings->set('enforce_admin', FALSE)
      ->save();

    // Check that the user can access the page after login.
    $this->drupalGet('admin/reports/status');
    $this->assertResponse(200, 'Admin page is accessible');
    $this->assertText(t("Here you can find a short overview of your site's parameters as well as any problems detected with your installation."), 'User can access elements of the admin page.');

    // Wait for timeout period to elapse.
    sleep(30);

    // Check we are now logged out.
    $this->drupalGet('admin/reports/status');
    $this->assertResponse(403, 'Admin page returns 403 access denied.');
    $this->assertNoText(t('Log out'), 'User is no longer logged in.');
    $this->assertNoText(t("Here you can find a short overview of your site's parameters as well as any problems detected with your installation."), 'User cannot access elements of the admin page.');
    $this->assertText(t('You have been logged out due to inactivity.'), 'User sees inactivity message.');
  }

  /**
   * Test integration with the remember me module.
   *
   * Users who checked remember_me on login should never be logged out.
   */
  public function testNoAutologoutWithRememberMe() {
    // Set the remember_me module data bit to TRUE.
    $this->userData->set('remember_me', $this->privilegedUser->id(), 'remember_me', TRUE);

    // Check that the user can access the page after login.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Homepage is accessible');
    $this->assertText(t('Log out'), 'User is still logged in.');

    // Wait for timeout period to elapse.
    sleep(30);

    // Check we are still logged in.
    $this->drupalGet('node');
    $this->assertResponse(200, 'Homepage is accessible');
    $this->assertText(t('Log out'), 'User is still logged in after timeout with remember_me on.');
  }

  /**
   * Test the behaviour of custom message displayed on autologout.
   */
  public function testCustomMessage() {
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    $inactivity_message = 'Custom message for test';

    // Update message string in configuration.
    $autologout_settings->set('inactivity_message', $inactivity_message)
      ->save();

    // Set time out for 10 seconds.
    $autologout_settings->set('timeout', 10)
      ->save();

    // Wait for 20 seconds for timeout.
    sleep(30);

    // Access the admin page and verify user is logged out and custom message
    // is displayed.
    $this->drupalGet('admin/reports/status');
    $this->assertText($inactivity_message, 'User sees custom message');
  }

  /**
   * Test the behaviour of application when Autologout is enabled for admin.
   */
  public function testAutologoutAdminPages() {

    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    // Enforce auto logout of admin pages.
    $autologout_settings->set('enforce_admin', TRUE)
      ->save();
    // Set time out as 10 seconds.
    $autologout_settings->set('timeout', 10)
      ->save();
    // Verify admin should not be logged out.
    $this->drupalGet('admin/reports/status');
    $this->assertResponse('200', 'Admin pages are accessible');

    // Wait until timeout.
    sleep(30);

    // Verify admin should be logged out.
    $this->drupalGet('admin/reports/status');
    $this->assertText(t('You have been logged out due to inactivity.'), 'User sees inactivity message.');
  }

  /**
   * Assert the timeout for a particular user.
   *
   * @param int $uid
   *   User uid to assert the timeout for.
   * @param int $expected_timeout
   *   The expected timeout.
   * @param string $message
   *   The test message.
   * @param string $group
   *   The test grouping.
   */
  public function assertAutotimeout($uid, $expected_timeout, $message = '', $group = '') {
    return $this->assertEqual(\Drupal::service('autologout.manager')->getUserTimeout($uid), $expected_timeout, $message, $group);
  }

}
