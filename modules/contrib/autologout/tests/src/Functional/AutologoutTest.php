<?php

namespace Drupal\Tests\autologout\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the autologout's features.
 *
 * @description Ensures that the autologout module functions as expected
 *
 * @group Autologout
 */
class AutologoutTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'system',
    'user',
    'views',
    'autologout',
    'block',
  ];

  /**
   * User with admin rights.
   *
   * @var \Drupal\user\Entity\User
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
   * Performs any pre-requisite tasks that need to happen.
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

    $this->configFactory = $this->container->get('config.factory');
    $this->userData = $this->container->get('user.data');

    // For the purposes of the test, set the timeout periods to 10 seconds.
    $this->configFactory->getEditable('autologout.settings')
      ->set('timeout', 10)
      ->set('padding', 0)
      ->save();
    // Make node page default.
    $this->configFactory->getEditable('system.site')
      ->set('page.front', 'node')
      ->save();

    $this->drupalLogin($this->privilegedUser);
  }

  /**
   * Tests the precedence of the timeouts.
   *
   * This tests the following function:
   *  _autologout_get_user_timeout();
   */
  public function testAutologoutTimeoutPrecedence() {
    $autologout_settings = $this->configFactory
      ->getEditable('autologout.settings');
    $autologout_role_settings = $this->configFactory
      ->getEditable('autologout.role.authenticated');
    $uid = $this->privilegedUser->id();
    $autologout_user_settings = $this->container->get('user.data');

    // Default used if no role is specified.
    $autologout_settings->set('timeout', 100)
      ->set('role_logout', FALSE)
      ->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $this->assertAutotimeout(
      $uid,
      100,
      'User timeout uses default if no other option set'
    );

    // Default used if role selected but no user's role is selected.
    $autologout_settings->set('role_logout', TRUE)->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $this->assertAutotimeout(
      $uid,
      100,
      'User timeout uses default if role timeouts are used but not one of the current user.'
    );

    // Role timeout is used if user's role is selected.
    $autologout_settings->set('role_logout', TRUE)->save();
    $autologout_role_settings->set('enabled', TRUE)
      ->set('timeout', 200)
      ->save();
    $this->assertAutotimeout($uid, 200, 'User timeout uses role value');

    // Role timeout is used if user's role is selected.
    $autologout_settings->set('role_logout', TRUE)->save();
    $autologout_role_settings->set('enabled', TRUE)
      ->set('timeout', 0)
      ->save();
    $this->assertAutotimeout(
      $uid,
      0,
      'User timeout uses role value of 0 if set for one of the user roles.'
    );

    // Role timeout used if personal timeout is empty string.
    $autologout_settings->set('role_logout', TRUE)->save();
    $autologout_role_settings->set('enabled', TRUE)
      ->set('timeout', 200)
      ->save();
    $autologout_user_settings->set('autologout', $uid, 'timeout', '');
    $autologout_user_settings->set('autologout', $uid, 'enabled', FALSE);
    $this->assertAutotimeout(
      $uid,
      200,
      'User timeout uses role value if personal value is the empty string.'
    );

    // Default timeout used if personal timeout is empty string.
    $autologout_settings->set('role_logout', TRUE)->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $autologout_user_settings->set('autologout', $uid, 'timeout', '');
    $autologout_user_settings->set('autologout', $uid, 'enabled', FALSE);
    $this->assertAutotimeout(
      $uid,
      100,
      'User timeout uses default value if personal value is the empty string and no role timeout is specified.'
    );

    // Personal timeout used if set.
    $autologout_settings->set('role_logout', TRUE)->save();
    $autologout_role_settings->set('enabled', FALSE)
      ->set('timeout', 200)
      ->save();
    $autologout_user_settings->set('autologout', $uid, 'timeout', 300);
    $autologout_user_settings->set('autologout', $uid, 'enabled', TRUE);
    $this->assertAutotimeout(
      $uid,
      300,
      'User timeout uses default value if personal value is the empty string and no role timeout is specified.'
    );
  }

  /**
   * Tests a user is logged out after the default timeout period.
   */
  public function testAutologoutDefaultTimeout() {
    // Check that the user can access the page after login.
    $this->drupalGet('node');
    $this->assertSession()->statusCodeEquals(200);
    self::assertTrue($this->drupalUserIsLoggedIn($this->privilegedUser));

    // Wait for timeout period to elapse.
    sleep(15);

    // Check we are now logged out.
    $this->drupalGet('node');
    $this->assertSession()->statusCodeEquals(200);
    self::assertFalse($this->drupalUserIsLoggedIn($this->privilegedUser));
  }

  /**
   * Tests a user is logged out with the alternate logout method.
   */
  public function testAutologoutAlternateLogoutMethod() {
    // Test that alternate logout works as expected.
    $this->drupalGet('autologout_alt_logout');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains(
      t('You have been logged out due to inactivity.')
    );

    // Check further logout requests result in access denied.
    $this->drupalGet('autologout_alt_logout');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests a user is not logged out within the default timeout period.
   */
  public function testAutologoutNoLogoutInsideTimeout() {
    // Check that the user can access the page after login.
    $this->drupalGet('node');
    $this->assertSession()->statusCodeEquals(200);
    self::assertTrue($this->drupalUserIsLoggedIn($this->privilegedUser));

    // Wait within the timeout period.
    sleep(5);

    // Check we are still logged in.
    $this->drupalGet('node');
    $this->assertSession()->statusCodeEquals(200);
    self::assertTrue($this->drupalUserIsLoggedIn($this->privilegedUser));
  }

  /**
   * Tests the behaviour of the settings for submission.
   */
  public function testAutologoutSettingsForm() {
    $edit = [];
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    $autologout_settings->set('max_timeout', 1000)->save();

    $roles = user_roles(TRUE);
    // Unset authenticated, as it uses the default timeout value.
    unset($roles['authenticated']);

    // Test that it is possible to set a value above the max_timeout
    // threshold.
    $edit['timeout'] = 1500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = TRUE;
      $edit['table[' . $key . '][timeout]'] = 1200;
      $edit['table[' . $key . '][url]'] = '/user/login';
    }
    $edit['redirect_url'] = '/user/login';

    $this->drupalPostForm(
      'admin/config/people/autologout',
      $edit,
      t('Save configuration')
    );
    $this->assertSession()->pageTextContains(
      t('The configuration options have been saved.')
    );

    // Test that out of range values are picked up.
    $edit['timeout'] = 2500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = TRUE;
      $edit['table[' . $key . '][timeout]'] = 1200;
      $edit['table[' . $key . '][url]'] = '/user/login';
    }
    $edit['redirect_url'] = '/user/login';
    $this->drupalPostForm(
      'admin/config/people/autologout',
      $edit,
      t('Save configuration')
    );
    $this->assertSession()->pageTextNotContains(
      t('The configuration options have been saved.')
    );

    // Test that out of range values are picked up.
    $edit['timeout'] = 1500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = TRUE;
      $edit['table[' . $key . '][timeout]'] = 2500;
      $edit['table[' . $key . '][url]'] = '/user/login';
    }
    $edit['redirect_url'] = '/user/login';
    $this->drupalPostForm(
      'admin/config/people/autologout',
      $edit,
      t('Save configuration')
    );
    $this->assertSession()->pageTextNotContains(
      t('The configuration options have been saved.')
    );

    // Test that role timeouts are not validated for disabled roles.
    $edit['timeout'] = 1500;
    $edit['max_timeout'] = 2000;
    $edit['padding'] = 60;
    $edit['role_logout'] = TRUE;
    foreach ($roles as $key => $role) {
      $edit['table[' . $key . '][enabled]'] = FALSE;
      $edit['table[' . $key . '][timeout]'] = 1200;
      $edit['table[' . $key . '][url]'] = '/user/login';
    }
    $edit['redirect_url'] = '/user/login';

    $this->drupalPostForm(
      'admin/config/people/autologout',
      $edit,
      t('Save configuration')
    );
    $this->assertSession()->pageTextContains(
      t('The configuration options have been saved.')
    );
    $this->drupalPostForm('admin/config/people/autologout', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'), 'Unable to save autologout due to out of range role timeout for a role which is not enabled..');

    // Test clearing of users individual timeout when this becomes disabled.
    $uid = $this->privilegedUser->id();
    // Activate individual user timeout for user.
    $this->userData->set('autologout', $uid, 'timeout', 1600);

    // Turn off individual settings.
    $edit['no_individual_logout_threshold'] = TRUE;
    $this->drupalPostForm('admin/config/people/autologout', $edit, t('Save configuration'));

    // Expected is that default value is returned, not user-overriden value.
    $this->assertAutotimeout($uid, 1500, 'User timeout is cleared when setting no_individual_logout_threshold is activated.');

  }

  /**
   * Tests a user is logged out and denied access to admin pages.
   */
  public function testAutologoutDefaultTimeoutAccessDeniedToAdmin() {
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    // Enforce auto logout of admin pages.
    $autologout_settings->set('enforce_admin', FALSE)->save();

    // Check that the user can access the page after login.
    $this->drupalGet('admin/reports/status');
    $this->assertSession()->statusCodeEquals(200);
    self::assertTrue($this->drupalUserIsLoggedIn($this->privilegedUser));

    // Wait for timeout period to elapse.
    sleep(15);

    // Check we are now logged out.
    $this->drupalGet('admin/reports/status');
    $this->assertSession()->statusCodeEquals(403);
    self::assertFalse($this->drupalUserIsLoggedIn($this->privilegedUser));
    $this->assertSession()->pageTextContains(
      t('You have been logged out due to inactivity.')
    );
  }

  /**
   * Tests integration with the remember me module.
   *
   * Users who checked remember_me on login should never be logged out.
   */
  public function testNoAutologoutWithRememberMe() {
    // Set the remember_me module data bit to TRUE.
    $this->userData->set(
      'remember_me',
      $this->privilegedUser->id(),
      'remember_me',
      TRUE
    );

    // Check that the user can access the page after login.
    $this->drupalGet('node');
    $this->assertSession()->statusCodeEquals(200);
    self::assertTrue($this->drupalUserIsLoggedIn($this->privilegedUser));

    // Wait for timeout period to elapse.
    sleep(15);

    // Check we are still logged in.
    $this->drupalGet('node');
    $this->assertSession()->statusCodeEquals(200);
    self::assertTrue($this->drupalUserIsLoggedIn($this->privilegedUser));
  }

  /**
   * Tests the behaviour of custom message displayed on autologout.
   */
  public function testCustomMessage() {
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    $inactivity_message = 'Custom message for test';

    // Update message string in configuration.
    $autologout_settings->set('inactivity_message', $inactivity_message)
      ->save();

    // Set time out for 5 seconds.
    $autologout_settings->set('timeout', 5)->save();

    // Wait for 20 seconds for timeout.
    sleep(20);

    // Access the admin page and verify user is logged out and custom message
    // is displayed.
    $this->drupalGet('admin/reports/status');
    self::assertFalse($this->drupalUserIsLoggedIn($this->privilegedUser));
    $this->assertSession()->pageTextContains($inactivity_message);
  }

  /**
   * Tests the behaviour of application when Autologout is enabled for admin.
   */
  public function testAutologoutAdminPages() {
    $autologout_settings = $this->configFactory->getEditable('autologout.settings');
    // Enforce auto logout of admin pages.
    $autologout_settings->set('enforce_admin', TRUE)->save();
    // Set time out as 5 seconds.
    $autologout_settings->set('timeout', 5)->save();
    // Verify admin should not be logged out.
    $this->drupalGet('admin/reports/status');
    $this->assertSession()->statusCodeEquals('200');

    // Wait until timeout.
    sleep(20);

    // Verify admin should be logged out.
    $this->drupalGet('admin/reports/status');
    self::assertFalse($this->drupalUserIsLoggedIn($this->privilegedUser));
    $this->assertSession()->pageTextContains(
      t('You have been logged out due to inactivity.')
    );
  }

  /**
   * Asserts the timeout for a particular user.
   *
   * @param int $uid
   *   User uid to assert the timeout for.
   * @param int $expected_timeout
   *   The expected timeout.
   * @param string $message
   *   The test message.
   */
  public function assertAutotimeout($uid, $expected_timeout, $message = '') {
    self::assertEquals(
      $this->container->get('autologout.manager')->getUserTimeout($uid),
      $expected_timeout,
      $message
    );
  }

}
