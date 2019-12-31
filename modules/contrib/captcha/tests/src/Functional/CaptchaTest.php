<?php

namespace Drupal\Tests\captcha\Functional;

/**
 * Tests CAPTCHA main test case sensitivity.
 *
 * @group captcha
 */
class CaptchaTest extends CaptchaWebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'captcha_long_form_id_test'];

  /**
   * Testing the protection of the user log in form.
   */
  public function testCaptchaOnLoginForm() {
    // Create user and test log in without CAPTCHA.
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);
    // Log out again.
    $this->drupalLogout();

    // Set a CAPTCHA on login form.
    /* @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('captcha/Math');
    $captcha_point->enable()->save();

    // Check if there is a CAPTCHA on the login form (look for the title).
    $this->drupalGet('user');
    $this->assertCaptchaPresence(TRUE);

    // Try to log in, which should fail.
    $edit = [
      'name' => $user->getDisplayName(),
      'pass' => $user->pass_raw,
      'captcha_response' => '?',
    ];
    $this->drupalPostForm(NULL, $edit, t('Log in'), [], self::LOGIN_HTML_FORM_ID);
    // Check for error message.
    $this->assertText(self::CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE, 'CAPTCHA should block user login form', 'CAPTCHA');

    // And make sure that user is not logged in:
    // check for name and password fields on ?q=user.
    $this->drupalGet('user');
    $this->assertField('name', t('Username field found.'), 'CAPTCHA');
    $this->assertField('pass', t('Password field found.'), 'CAPTCHA');
  }

  /**
   * Assert function for testing if comment posting works as it should.
   *
   * Creates node with comment writing enabled, tries to post comment
   * with given CAPTCHA response (caller should enable the desired
   * challenge on page node comment forms) and checks if
   * the result is as expected.
   *
   * @param string $captcha_response
   *   The response on the CAPTCHA.
   * @param bool $should_pass
   *   Describing if the posting should pass or should be blocked.
   * @param string $message
   *   To prefix to nested asserts.
   */
  protected function assertCommentPosting($captcha_response, $should_pass, $message) {
    // Make sure comments on pages can be saved directly without preview.
    $this->container->get('state')
      ->set('comment_preview_page', DRUPAL_OPTIONAL);

    // Create a node with comments enabled.
    $node = $this->drupalCreateNode();

    // Post comment on node.
    $edit = $this->getCommentFormValues();
    $comment_subject = $edit['subject[0][value]'];
    $comment_body = $edit['comment_body[0][value]'];
    $edit['captcha_response'] = $captcha_response;
    $this->drupalPostForm('comment/reply/node/' . $node->id() . '/comment', $edit, t('Save'), [], 'comment-form');

    if ($should_pass) {
      // There should be no error message.
      $this->assertCaptchaResponseAccepted();
      // Get node page and check that comment shows up.
      $this->drupalGet('node/' . $node->id());
      $this->assertText($comment_subject, $message . ' Comment should show up on node page.', 'CAPTCHA');
      $this->assertText($comment_body, $message . ' Comment should show up on node page.', 'CAPTCHA');
    }
    else {
      // Check for error message.
      $this->assertText(self::CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE, $message . ' Comment submission should be blocked.', 'CAPTCHA');
      // Get node page and check that comment is not present.
      $this->drupalGet('node/' . $node->id());
      $this->assertNoText($comment_subject, $message . ' Comment should not show up on node page.', 'CAPTCHA');
      $this->assertNoText($comment_body, $message . ' Comment should not show up on node page.', 'CAPTCHA');
    }
  }

  /**
   * Testing the case sensitive/insensitive validation.
   */
  public function testCaseInsensitiveValidation() {
    $config = $this->config('captcha.settings');
    // Set Test CAPTCHA on comment form.
    captcha_set_form_id_setting(self::COMMENT_FORM_ID, 'captcha/Test');

    // Log in as normal user.
    $this->drupalLogin($this->normalUser);

    // Test case sensitive posting.
    $config->set('default_validation', CAPTCHA_DEFAULT_VALIDATION_CASE_SENSITIVE);
    $config->save();

    $this->assertCommentPosting('Test 123', TRUE, 'Case sensitive validation of right casing.');
    $this->assertCommentPosting('test 123', FALSE, 'Case sensitive validation of wrong casing.');
    $this->assertCommentPosting('TEST 123', FALSE, 'Case sensitive validation of wrong casing.');

    // Test case insensitive posting (the default).
    $config->set('default_validation', CAPTCHA_DEFAULT_VALIDATION_CASE_INSENSITIVE);
    $config->save();

    $this->assertCommentPosting('Test 123', TRUE, 'Case insensitive validation of right casing.');
    $this->assertCommentPosting('test 123', TRUE, 'Case insensitive validation of wrong casing.');
    $this->assertCommentPosting('TEST 123', TRUE, 'Case insensitive validation of wrong casing.');
  }

  /**
   * Test if the CAPTCHA description is only shown with  challenge widgets.
   *
   * For example, when a comment is previewed with correct CAPTCHA answer,
   * a challenge is generated and added to the form but removed in the
   * pre_render phase. The CAPTCHA description should not show up either.
   *
   * @see testCaptchaSessionReuseOnNodeForms()
   */
  public function testCaptchaDescriptionAfterCommentPreview() {
    // Set Test CAPTCHA on comment form.
    captcha_set_form_id_setting(self::COMMENT_FORM_ID, 'captcha/Test');

    // Log in as normal user.
    $this->drupalLogin($this->normalUser);

    // Create a node with comments enabled.
    $node = $this->drupalCreateNode();

    // Preview comment with correct CAPTCHA answer.
    $edit = $this->getCommentFormValues();
    $edit['captcha_response'] = 'Test 123';
    $this->drupalPostForm('comment/reply/node/' . $node->id() . '/comment', $edit, t('Preview'));

    // Check that there is no CAPTCHA after preview.
    $this->assertCaptchaPresence(FALSE);
  }

  /**
   * Test if the CAPTCHA session ID is reused when previewing nodes.
   *
   * Node preview after correct response should not show CAPTCHA anymore.
   * The preview functionality of comments and nodes works
   * slightly different under the hood.
   * CAPTCHA module should be able to handle both.
   *
   * @see testCaptchaDescriptionAfterCommentPreview()
   */
  public function testCaptchaSessionReuseOnNodeForms() {
    // Set Test CAPTCHA on page form.
    captcha_set_form_id_setting('node_page_form', 'captcha/Test');

    // Log in as normal user.
    $this->drupalLogin($this->normalUser);

    // Page settings to post, with correct CAPTCHA answer.
    $edit = $this->getNodeFormValues();
    $edit['captcha_response'] = 'Test 123';
    $this->drupalGet('node/add/page');
    $this->drupalPostForm(NULL, $edit, t('Preview'));

    $this->assertCaptchaPresence(FALSE);
  }

  /**
   * CAPTCHA should be put on admin pages even if visitor has no access.
   */
  public function testCaptchaOnLoginBlockOnAdminPagesIssue893810() {
    // Set a CAPTCHA on login block form.
    /* @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->setCaptchaType('captcha/Math');
    $captcha_point->enable()->save();

    // Enable the user login block.
    $this->drupalPlaceBlock('user_login_block', ['id' => 'login']);

    // Check if there is a CAPTCHA on home page.
    $this->drupalGet('');
    $this->assertCaptchaPresence(TRUE);

    // Check there is a CAPTCHA on "forbidden" admin pages.
    $this->drupalGet('admin');
    $this->assertCaptchaPresence(TRUE);
  }

  /**
   * Test that forms with IDs exceeding 64 characters can be assigned captchas.
   */
  public function testLongFormId() {
    // We add the form manually so we can mimic the character
    // truncation of the label field as formId.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(self::CAPTCHA_ADMIN_PATH);

    $label = 'this_formid_is_intentionally_longer_than_64_characters_to_test_captcha';
    // Truncated to 64 chars so it can be a machine name.
    $formId = substr($label, 0, 64);

    $form_values = [
      'label' => $label,
      'formId' => $formId,
      'captchaType' => 'captcha/Math',
    ];

    // Create intentionally long id Captcha Point.
    $this->drupalPostForm(self::CAPTCHA_ADMIN_PATH . '/captcha-points/add', $form_values, t('Save'));
    $this->assertRaw(t('Captcha Point for %label form was created.', ['%label' => $formId]));

    // We need to log out to test the captcha.
    $this->drupalLogout();

    // Navigate to the form with a >64 char id and confirm there is Captcha.
    $this->drupalGet('captcha/test_form/long_id');
    $this->assertCaptchaPresence(TRUE);
  }

}
