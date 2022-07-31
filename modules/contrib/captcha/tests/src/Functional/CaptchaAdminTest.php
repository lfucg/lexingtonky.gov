<?php

namespace Drupal\Tests\captcha\Functional;

use Drupal\captcha\Entity\CaptchaPoint;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests CAPTCHA admin settings.
 *
 * @group captcha
 */
class CaptchaAdminTest extends CaptchaWebTestBase {

  use StringTranslationTrait;

  /**
   * Test access to the admin pages.
   */
  public function testAdminAccess() {
    $this->drupalLogin($this->normalUser);
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH);
    $this->assertSession()->pageTextContains($this->t('Access denied'));

    $this->drupalLogin($this->adminUser);
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH);
    $this->assertSession()->pageTextNotContains($this->t('Access denied'));
  }

  /**
   * Test the CAPTCHA point setting getter/setter.
   */
  public function testCaptchaPointSettingGetterAndSetter() {
    $comment_form_id = self::COMMENT_FORM_ID;
    captcha_set_form_id_setting($comment_form_id, 'test');
    /** @var \Drupal\captcha\Entity\CaptchaPoint $result */
    $result = captcha_get_form_id_setting($comment_form_id);
    $this->assertNotNull($result, 'CAPTCHA exists');
    $this->assertEquals($result->getCaptchaType(), 'test', 'CAPTCHA type: default');
    $result = captcha_get_form_id_setting($comment_form_id, TRUE);
    $this->assertNotNull($result, 'CAPTCHA exists');
    $this->assertEquals($result, 'test', 'Setting and symbolic getting CAPTCHA point: "test"');

    // Set to 'default'.
    captcha_set_form_id_setting($comment_form_id, 'default');
    $this->config('captcha.settings')
      ->set('default_challenge', 'foo/bar')
      ->save();
    $result = captcha_get_form_id_setting($comment_form_id);
    $this->assertNotNull($result, 'CAPTCHA exists');
    $this->assertEquals($result->getCaptchaType(), 'foo/bar', 'Setting and getting CAPTCHA point: default');
    $result = captcha_get_form_id_setting($comment_form_id, TRUE);
    $this->assertNotNull($result, 'Setting and symbolic getting CAPTCHA point: "default"');
    $this->assertEquals($result, 'foo/bar', 'Setting and symbolic getting CAPTCHA point: default');

    // Set to 'baz/boo'.
    captcha_set_form_id_setting($comment_form_id, 'baz/boo');
    $result = captcha_get_form_id_setting($comment_form_id);
    $this->assertNotNull($result, 'CAPTCHA exists');
    $this->assertEquals($result->getCaptchaType(), 'baz/boo', 'Setting and getting CAPTCHA point: baz/boo');
    $result = captcha_get_form_id_setting($comment_form_id, TRUE);
    $this->assertEquals($result, 'baz/boo', 'Setting and symbolic getting CAPTCHA point: "baz/boo"');

    // Set to NULL (which should delete the CAPTCHA point setting entry).
    captcha_set_form_id_setting($comment_form_id, NULL);
    $result = captcha_get_form_id_setting($comment_form_id);
    $this->assertNotNull($result, 'CAPTCHA exists');
    $this->assertEquals($result->getCaptchaType(), 'foo/bar', 'Setting and getting CAPTCHA point: NULL');
    $result = captcha_get_form_id_setting($comment_form_id, TRUE);
    $this->assertNotNull($result, 'CAPTCHA exists');

    // Set with object.
    $captcha_type = 'baba/fofo';
    captcha_set_form_id_setting($comment_form_id, $captcha_type);

    $result = captcha_get_form_id_setting($comment_form_id);
    $this->assertNotNull($result, 'Setting and getting CAPTCHA point: baba/fofo');
    // $this->assertEqual($result->module, 'baba', 'Setting and getting
    // CAPTCHA point: baba/fofo', 'CAPTCHA');.
    $this->assertEquals($result->getCaptchaType(), 'baba/fofo', 'Setting and getting CAPTCHA point: baba/fofo');
    $result = captcha_get_form_id_setting($comment_form_id, TRUE);
    $this->assertEquals($result, 'baba/fofo', 'Setting and symbolic getting CAPTCHA point: "baba/fofo"');
  }

  /**
   * Helper function for checking CAPTCHA setting of a form.
   *
   * @param string $form_id
   *   The form_id of the form to investigate.
   * @param string $challenge_type
   *   What the challenge type should be:
   *   NULL, 'default' or something like 'captcha/Math'.
   */
  protected function assertCaptchaSetting($form_id, $challenge_type) {
    $result = captcha_get_form_id_setting(self::COMMENT_FORM_ID, TRUE);
    $this->assertEquals($result, $challenge_type,
      $this->t('Check CAPTCHA setting for form: expected: @expected, received: @received.',
        [
          '@expected' => var_export($challenge_type, TRUE),
          '@received' => var_export($result, TRUE),
        ]));
  }

  /**
   * Testing of the CAPTCHA administration links.
   */
  public function testCaptchaAdminLinks() {
    $this->drupalLogin($this->adminUser);

    // Enable CAPTCHA administration links.
    $edit = [
      'administration_mode' => TRUE,
    ];

    $this->drupalGet(self::CAPTCHA_ADMIN_PATH);
    $this->submitForm($edit, $this->t('Save configuration'));

    // Create a node with comments enabled.
    $node = $this->drupalCreateNode();

    // Go to node page.
    $this->drupalGet('node/' . $node->id());

    // Click the add new comment link.
    $this->clickLink($this->t('Add new comment'));
    $add_comment_url = $this->getUrl();

    // Remove fragment part from comment URL to avoid
    // problems with later asserts.
    $add_comment_url = strtok($add_comment_url, "#");

    // Click the CAPTCHA admin link to enable a challenge.
    $this->clickLink($this->t('Place a CAPTCHA here for untrusted users.'));

    // Enable Math CAPTCHA.
    $edit = ['captchaType' => 'captcha/Math'];
    $this->drupalGet($this->getUrl());
    $this->submitForm($edit, $this->t('Save'));

    // Check if returned to original comment form.
    $this->assertSession()->addressEquals($add_comment_url);

    // Check if CAPTCHA was successfully enabled
    // (on CAPTCHA admin links fieldset).
    $this->assertSession()->pageTextContains($this->t('CAPTCHA: challenge "@type" enabled', ['@type' => $edit['captchaType']]));

    // Check if CAPTCHA was successfully enabled (through API).
    $this->assertCaptchaSetting(self::COMMENT_FORM_ID, 'captcha/Math');

    // Edit challenge type through CAPTCHA admin links.
    $this->clickLink($this->t('change'));

    // Enable Math CAPTCHA.
    $edit = ['captchaType' => 'default'];
    $this->drupalGet($this->getUrl());
    $this->submitForm($edit, 'Save');

    // Check if returned to original comment form.
    $this->assertEquals($add_comment_url, $this->getUrl(),
      'After editing challenge type CAPTCHA admin links: should return to original form.');

    // Check if CAPTCHA was successfully changed
    // (on CAPTCHA admin links fieldset).
    // This is actually the same as the previous setting because
    // the captcha/Math is the default for the default challenge.
    // @todo Make sure the edit is a real change.
    $this->assertSession()->pageTextContains($this->t('CAPTCHA: challenge "@type" enabled', ['@type' => $edit['captchaType']]));
    // Check if CAPTCHA was successfully edited (through API).
    $this->assertCaptchaSetting(self::COMMENT_FORM_ID, 'default');

    // Disable challenge through CAPTCHA admin links.
    $this->drupalGet(Url::fromRoute('entity.captcha_point.disable', ['captcha_point' => self::COMMENT_FORM_ID]));
    $this->submitForm([], $this->t('Disable'));

    // Check if returned to captcha point list.
    global $base_url;
    $this->assertEquals($base_url . '/admin/config/people/captcha/captcha-points', $this->getUrl(),
      'After disabling challenge in CAPTCHA admin: should return to captcha point list.');

    // Check if CAPTCHA was successfully disabled
    // (on CAPTCHA admin links fieldset).
    $this->assertSession()->responseContains($this->t('Captcha point %form_id has been disabled.', ['%form_id' => self::COMMENT_FORM_ID]));
  }

  /**
   * Test untrusted user posting.
   */
  public function testUntrustedUserPosting() {
    // Set CAPTCHA on comment form.
    captcha_set_form_id_setting(self::COMMENT_FORM_ID, 'captcha/Math');

    // Create a node with comments enabled.
    $node = $this->drupalCreateNode();

    // Log in as normal (untrusted) user.
    $this->drupalLogin($this->normalUser);

    // Go to node page and click the "add comment" link.
    $this->drupalGet('node/' . $node->id());
    $this->clickLink($this->t('Add new comment'));
    $add_comment_url = $this->getUrl();

    // Check if CAPTCHA is visible on form.
    $this->assertCaptchaPresence(TRUE);
    // Try to post a comment with wrong answer.
    $edit = $this->getCommentFormValues();
    $edit['captcha_response'] = 'xx';
    $this->drupalGet($add_comment_url);
    $this->submitForm($edit, $this->t('Preview'));
    $this->assertSession()->pageTextContains(self::CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE);
  }

  /**
   * Test XSS vulnerability on CAPTCHA description.
   */
  public function testXssOnCaptchaDescription() {
    // Set CAPTCHA on user register form.
    captcha_set_form_id_setting('user_register', 'captcha/Math');

    // Put JavaScript snippet in CAPTCHA description.
    $this->drupalLogin($this->adminUser);
    $xss = '<script type="text/javascript">alert("xss")</script>';
    $edit = ['description' => $xss];
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH);
    $this->submitForm($edit, $this->t('Save configuration'));

    // Visit user register form and check if JavaScript snippet is there.
    $this->drupalLogout();
    $this->drupalGet('user/register');
    $this->assertSession()->responseNotContains($xss);
  }

  /**
   * Test the CAPTCHA placement clearing.
   */
  public function testCaptchaPlacementCacheClearing() {
    // Set CAPTCHA on user register form.
    captcha_set_form_id_setting('user_register_form', 'captcha/Math');
    // Visit user register form to fill the CAPTCHA placement cache.
    $this->drupalGet('user/register');
    // Check if there is CAPTCHA placement cache.
    $placement_map = $this->container->get('cache.default')
      ->get('captcha_placement_map_cache');
    $this->assertNotNull($placement_map, 'CAPTCHA placement cache should be set.');
    // Clear the cache.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH);
    $this->submitForm([], $this->t('Clear the CAPTCHA placement cache'));

    // Check that the placement cache is unset.
    $placement_map = $this->container->get('cache.default')
      ->get('captcha_placement_map_cache');
    $this->assertFalse($placement_map, 'CAPTCHA placement cache should be unset after cache clear.');
  }

  /**
   * Helper function to get CAPTCHA point setting straight from the database.
   *
   * @param string $form_id
   *   Form machine ID.
   *
   * @return \Drupal\captcha\Entity\CaptchaPoint
   *   CaptchaPoint with mysql query result.
   */
  protected function getCaptchaPointSettingFromDatabase($form_id) {
    $ids = \Drupal::entityQuery('captcha_point')
      ->condition('formId', $form_id)
      ->execute();
    return $ids ? CaptchaPoint::load(reset($ids)) : NULL;
  }

  /**
   * Method for testing the CAPTCHA point administration.
   */
  public function testCaptchaPointAdministration() {
    // Generate CAPTCHA point data:
    // Drupal form ID should consist of lowercase alphanumerics and underscore).
    $captcha_point_form_id = 'form_' . strtolower($this->randomMachineName(32));
    // The Math CAPTCHA by the CAPTCHA module is always available,
    // so let's use it.
    $captcha_point_module = 'captcha';
    $captcha_point_type = 'Math';

    // Log in as admin.
    $this->drupalLogin($this->adminUser);
    $label = 'TEST';

    // Try and set CAPTCHA point without the #required label. Should fail.
    $form_values = [
      'formId' => $captcha_point_form_id,
      'captchaType' => $captcha_point_module . '/' . $captcha_point_type,
    ];
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/add');
    $this->submitForm($form_values, 'Save');
    $this->assertSession()->pageTextContains($this->t('Form ID field is required.'));

    // Set CAPTCHA point through admin/user/captcha/captcha/captcha_point.
    $form_values['label'] = $label;
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/add');
    $this->submitForm($form_values, $this->t('Save'));
    $this->assertSession()->responseContains($this->t('Captcha Point for %label form was created.', ['%label' => $captcha_point_form_id]));

    // Check in database.
    /** @var \Drupal\captcha\Entity\CaptchaPoint result */
    $result = $this->getCaptchaPointSettingFromDatabase($captcha_point_form_id);
    $this->assertEquals($result->captchaType, $captcha_point_module . '/' . $captcha_point_type,
      'Enabled CAPTCHA point should have module and type set');

    // Disable CAPTCHA point again.
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/' . $captcha_point_form_id . '/disable');
    $this->submitForm([], $this->t('Disable'));
    $this->assertSession()->responseContains($this->t('Captcha point %label has been disabled.', ['%label' => $label]));

    // Check in database.
    $result = $this->getCaptchaPointSettingFromDatabase($captcha_point_form_id);
    $this->assertInstanceOf(CaptchaPoint::class, $result, 'Disabled CAPTCHA point should be in database');
    $this->assertFalse($result->status());

    // Set CAPTCHA point via admin/user/captcha/captcha/captcha_point/$form_id.
    $form_values = [
      'captchaType' => $captcha_point_module . '/' . $captcha_point_type,
    ];
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/' . $captcha_point_form_id);
    $this->submitForm($form_values, $this->t('Save'));
    $this->assertSession()->responseContains($this->t('Captcha Point for %form_id form was updated.', ['%form_id' => $captcha_point_form_id]));

    // Check in database.
    $result = $this->getCaptchaPointSettingFromDatabase($captcha_point_form_id);
    $this->assertEquals($result->captchaType, $captcha_point_module . '/' . $captcha_point_type,
      'Enabled CAPTCHA point should have module and type set');

    // Delete CAPTCHA point.
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/' . $captcha_point_form_id . '/delete');
    $this->submitForm([], $this->t('Delete'));
    $this->assertSession()->responseContains($this->t('Captcha point %label has been deleted.', ['%label' => $label]));

    $result = $this->getCaptchaPointSettingFromDatabase($captcha_point_form_id);
    $this->assertNull($result, 'Deleted CAPTCHA point should not be in database');
  }

  /**
   * Method for testing the CAPTCHA point administration.
   */
  public function testCaptchaPointAdministrationByNonAdmin() {
    // First add a CAPTCHA point (as admin).
    $captcha_point_form_id = 'form_' . strtolower($this->randomMachineName(32));
    $captcha_point_module = 'captcha';
    $captcha_point_type = 'Math';
    $label = 'TEST_2';

    $this->drupalLogin($this->adminUser);

    $form_values = [
      'label' => $label,
      'formId' => $captcha_point_form_id,
      'captchaType' => $captcha_point_module . '/' . $captcha_point_type,
    ];
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/add');
    $this->submitForm($form_values, $this->t('Save'));
    $this->assertSession()->responseContains($this->t('Captcha Point for %form_id form was created.', ['%form_id' => $captcha_point_form_id]));

    // Switch from admin to non-admin.
    $this->drupalLogin($this->normalUser);

    // Try to set CAPTCHA point
    // through admin/user/captcha/captcha/captcha_point.
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points');
    $this->assertSession()->pageTextContains($this->t('You are not authorized to access this page.'));

    // Try to disable the CAPTCHA point.
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/' . $captcha_point_form_id . '/disable');
    $this->assertSession()->pageTextContains($this->t('You are not authorized to access this page.'));

    // Try to delete the CAPTCHA point.
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/' . $captcha_point_form_id . '/delete');
    $this->assertSession()->pageTextContains($this->t('You are not authorized to access this page.'));

    // Switch from nonadmin to admin again.
    $this->drupalLogin($this->adminUser);

    // Check if original CAPTCHA point still exists in database.
    $result = $this->getCaptchaPointSettingFromDatabase($captcha_point_form_id);
    $this->assertEquals($result->captchaType, $captcha_point_module . '/' . $captcha_point_type, 'Enabled CAPTCHA point should have module and type set');

    // Delete captcha point.
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH . '/captcha-points/' . $captcha_point_form_id . '/delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->responseContains($this->t('Captcha point %label has been deleted.', ['%label' => $label]));
  }

}
