<?php

namespace Drupal\Tests\captcha\Functional;

/**
 * Tests CAPTCHA caching on various pages.
 *
 * @group captcha
 */
class CaptchaCacheTest extends CaptchaWebTestBase {

  /**
   * Modules to install for this Test class.
   *
   * @var array
   */
  public static $modules = ['block', 'image_captcha'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('user_login_block', ['id' => 'login']);
  }

  /**
   * Test the cache tags.
   */
  public function testCacheTags() {
    global $base_path;
    // Check caching without captcha as anonymous user.
    $this->drupalGet('');
    $this->assertEqual($this->drupalGetHeader('x-drupal-cache'), 'MISS');
    $this->drupalGet('');
    $this->assertEqual($this->drupalGetHeader('x-drupal-cache'), 'HIT');

    // Enable captcha on login block and test caching.
    captcha_set_form_id_setting('user_login_form', 'captcha/Math');
    $this->drupalGet('');
    $sid = $this->getCaptchaSidFromForm();
    $this->assertNull($this->drupalGetHeader('x-drupal-cache'), 'Cache is disabled');
    $this->drupalGet('');
    $this->assertNotEqual($sid, $this->getCaptchaSidFromForm());

    // Switch challenge to captcha/Test, check the captcha isn't cached.
    captcha_set_form_id_setting('user_login_form', 'captcha/Test');
    $this->drupalGet('');
    $sid = $this->getCaptchaSidFromForm();
    $this->assertNull($this->drupalGetHeader('x-drupal-cache'), 'Cache is disabled');
    $this->drupalGet('');
    $this->assertNotEqual($sid, $this->getCaptchaSidFromForm());

    // Switch challenge to image_captcha/Image, check the captcha isn't cached.
    captcha_set_form_id_setting('user_login_form', 'image_captcha/Image');
    $this->drupalGet('');
    $image_path = $this->getSession()->getPage()->find('css', '.captcha img')->getAttribute('src');
    $this->assertNull($this->drupalGetHeader('x-drupal-cache'), 'Cache disabled');
    // Check that we get a new image when vising the page again.
    $this->drupalGet('');
    $this->assertNotEqual($image_path, $this->getSession()->getPage()->find('css', '.captcha img')->getAttribute('src'));
    // Check image caching, remove the base path since drupalGet() expects the
    // internal path.
    $this->drupalGet(substr($image_path, strlen($base_path)));
    $this->assertResponse(200);
    // Request image twice to make sure no errors happen (due to page caching).
    $this->drupalGet(substr($image_path, strlen($base_path)));
    $this->assertResponse(200);
  }

}
