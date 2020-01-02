<?php

namespace Drupal\captcha\Tests;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\simpletest\WebTestBase;

/**
 * Tests CAPTCHA session reusing.
 *
 * @group captcha
 */
class CaptchaSessionReuseAttackTestCase extends WebTestBase {

  use CommentTestTrait;

  /**
   * Wrong response error message.
   */
  const CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE = 'The answer you entered for the CAPTCHA was not correct.';

  /**
   * Unknown CSID error message.
   */
  const CAPTCHA_UNKNOWN_CSID_ERROR_MESSAGE = 'CAPTCHA validation error: unknown CAPTCHA session ID. Contact the site administrator if this problem persists.';

  /**
   * Modules to install for this Test class.
   *
   * @var array
   */
  public static $modules = ['captcha', 'comment'];


  /**
   * User with various administrative permissions.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Normal visitor with limited permissions.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $normalUser;

  /**
   * Form ID of comment form on standard (page) node.
   */
  const COMMENT_FORM_ID = 'comment_comment_form';

  const LOGIN_HTML_FORM_ID = 'user-login-form';

  /**
   * Drupal path of the (general) CAPTCHA admin page.
   */
  const CAPTCHA_ADMIN_PATH = 'admin/config/people/captcha';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    // Load two modules: the captcha module itself and the comment
    // module for testing anonymous comments.
    parent::setUp();
    module_load_include('inc', 'captcha');

    $this->drupalCreateContentType(['type' => 'page']);

    // Create a normal user.
    $permissions = [
      'access comments',
      'post comments',
      'skip comment approval',
      'access content',
      'create page content',
      'edit own page content',
    ];
    $this->normalUser = $this->drupalCreateUser($permissions);

    // Create an admin user.
    $permissions[] = 'administer CAPTCHA settings';
    $permissions[] = 'skip CAPTCHA';
    $permissions[] = 'administer permissions';
    $permissions[] = 'administer content types';
    $this->adminUser = $this->drupalCreateUser($permissions);

    // Open comment for page content type.
    $this->addDefaultCommentField('node', 'page');

    // Put comments on page nodes on a separate page.
    $comment_field = FieldConfig::loadByName('node', 'page', 'comment');
    $comment_field->setSetting('form_location', CommentItemInterface::FORM_SEPARATE_PAGE);
    $comment_field->save();

    /* @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()
      ->getStorage('captcha_point')
      ->load('user_login_form');
    $captcha_point->enable()->save();
    $this->config('captcha.settings')
      ->set('default_challenge', 'captcha/test')
      ->save();
  }

  /**
   * Assert that the response is accepted.
   *
   * No "unknown CSID" message, no "CSID reuse attack detection" message,
   * No "wrong answer" message.
   */
  protected function assertCaptchaResponseAccepted() {
    // There should be no error message about unknown CAPTCHA session ID.
    $this->assertNoText(self::CAPTCHA_UNKNOWN_CSID_ERROR_MESSAGE,
      'CAPTCHA response should be accepted (known CSID).',
      'CAPTCHA'
    );
    // There should be no error message about wrong response.
    $this->assertNoText(self::CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE,
      'CAPTCHA response should be accepted (correct response).',
      'CAPTCHA'
    );
  }

  /**
   * Assert that there is a CAPTCHA on the form or not.
   *
   * @param bool $presence
   *   Whether there should be a CAPTCHA or not.
   */
  protected function assertCaptchaPresence($presence) {
    if ($presence) {
      $this->assertText(_captcha_get_description(),
        'There should be a CAPTCHA on the form.', 'CAPTCHA'
      );
    }
    else {
      $this->assertNoText(_captcha_get_description(),
        'There should be no CAPTCHA on the form.', 'CAPTCHA'
      );
    }
  }

  /**
   * Helper function to generate a form values array for comment forms.
   */
  protected function getCommentFormValues() {
    $edit = [
      'subject[0][value]' => 'comment_subject ' . $this->randomMachineName(32),
      'comment_body[0][value]' => 'comment_body ' . $this->randomMachineName(256),
    ];

    return $edit;
  }

  /**
   * Helper function to generate a form values array for node forms.
   */
  protected function getNodeFormValues() {
    $edit = [
      'title[0][value]' => 'node_title ' . $this->randomMachineName(32),
      'body[0][value]' => 'node_body ' . $this->randomMachineName(256),
    ];

    return $edit;
  }

  /**
   * Get the CAPTCHA session id from the current form in the browser.
   *
   * @param null|string $form_html_id
   *   HTML form id attribute.
   *
   * @return int
   *   Captcha SID integer.
   */
  protected function getCaptchaSidFromForm($form_html_id = NULL) {
    if (!$form_html_id) {
      $elements = $this->xpath('//input[@name="captcha_sid"]');
    }
    else {
      $elements = $this->xpath('//form[@id="' . $form_html_id . '"]//input[@name="captcha_sid"]');
    }
    $captcha_sid = (int) $elements[0]['value'];

    return $captcha_sid;
  }

  /**
   * Get the CAPTCHA token from the current form in the browser.
   *
   * @param null|string $form_html_id
   *   HTML form id attribute.
   *
   * @return int
   *   Captcha token integer.
   */
  protected function getCaptchaTokenFromForm($form_html_id = NULL) {
    if (!$form_html_id) {
      $elements = $this->xpath('//input[@name="captcha_token"]');
    }
    else {
      $elements = $this->xpath('//form[@id="' . $form_html_id . '"]//input[@name="captcha_token"]');
    }
    $captcha_token = (int) $elements[0]['value'];

    return $captcha_token;
  }

  /**
   * Get the solution of the math CAPTCHA from the current form in the browser.
   *
   * @param null|string $form_html_id
   *   HTML form id attribute.
   *
   * @return int
   *   Calculated Math solution.
   */
  protected function getMathCaptchaSolutionFromForm($form_html_id = NULL) {
    // Get the math challenge.
    if (!$form_html_id) {
      $elements = $this->xpath('//div[contains(@class, "form-item-captcha-response")]/span[@class="field-prefix"]');
    }
    else {
      $elements = $this->xpath('//form[@id="' . $form_html_id . '"]//div[contains(@class, "form-item-captcha-response")]/span[@class="field-prefix"]');
    }
    $this->assert('pass', json_encode($elements));
    $challenge = (string) $elements[0];
    $this->assert('pass', $challenge);
    // Extract terms and operator from challenge.
    $matches = [];
    preg_match('/\\s*(\\d+)\\s*(-|\\+)\\s*(\\d+)\\s*=\\s*/', $challenge, $matches);
    // Solve the challenge.
    $a = (int) $matches[1];
    $b = (int) $matches[3];
    $solution = $matches[2] == '-' ? $a - $b : $a + $b;

    return $solution;
  }

  /**
   * Helper function to allow comment posting for anonymous users.
   */
  protected function allowCommentPostingForAnonymousVisitors() {
    // Enable anonymous comments.
    user_role_grant_permissions(AccountInterface::ANONYMOUS_ROLE, [
      'access comments',
      'post comments',
      'skip comment approval',
    ]);
  }

  /**
   * Assert that the CAPTCHA session ID reuse attack was detected.
   */
  protected function assertCaptchaSessionIdReuseAttackDetection() {
    // There should be an error message about wrong response.
    $this->assertText(self::CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE,
      'CAPTCHA response should flagged as wrong.',
      'CAPTCHA'
    );
  }

  /**
   * Test captcha attack detection on comment form.
   */
  public function testCaptchaSessionReuseAttackDetectionOnCommentPreview() {
    // Create commentable node.
    $node = $this->drupalCreateNode();
    // Set Test CAPTCHA on comment form.
    captcha_set_form_id_setting(self::COMMENT_FORM_ID, 'captcha/Test');
    $this->config('captcha.settings')
      ->set('persistence', CAPTCHA_PERSISTENCE_SKIP_ONCE_SUCCESSFUL_PER_FORM_INSTANCE)
      ->save();

    // Log in as normal user.
    $this->drupalLogin($this->normalUser);

    // Go to comment form of commentable node.
    $this->drupalGet('comment/reply/node/' . $node->id() . '/comment');
    $this->assertCaptchaPresence(TRUE);

    // Get CAPTCHA session ID and solution of the challenge.
    $captcha_sid = $this->getCaptchaSidFromForm();
    $captcha_token = $this->getCaptchaTokenFromForm();
    $solution = "Test 123";

    // Post the form with the solution.
    $edit = $this->getCommentFormValues();
    $edit['captcha_response'] = $solution;
    $this->drupalPostForm(NULL, $edit, t('Preview'));
    // Answer should be accepted and further CAPTCHA omitted.
    $this->assertCaptchaResponseAccepted();
    $this->assertCaptchaPresence(FALSE);

    // Post a new comment, reusing the previous CAPTCHA session.
    $edit = $this->getCommentFormValues();
    $edit['captcha_sid'] = $captcha_sid;
    $edit['captcha_token'] = $captcha_token;
    $edit['captcha_response'] = $solution;
    $this->drupalPostForm('comment/reply/node/' . $node->id() . '/comment', $edit, t('Preview'));
    // CAPTCHA session reuse attack should be detected.
    $this->assertCaptchaSessionIdReuseAttackDetection();
    // There should be a CAPTCHA.
    $this->assertCaptchaPresence(TRUE);
  }

  /**
   * Test captcha attach detection on node form.
   */
  public function testCaptchaSessionReuseAttackDetectionOnNodeForm() {
    // Set CAPTCHA on page form.
    captcha_set_form_id_setting('node_page_form', 'captcha/Test');
    $this->config('captcha.settings')
      ->set('persistence', CAPTCHA_PERSISTENCE_SKIP_ONCE_SUCCESSFUL_PER_FORM_INSTANCE)
      ->save();

    // Log in as normal user.
    $this->drupalLogin($this->normalUser);

    // Go to node add form.
    $this->drupalGet('node/add/page');
    $this->assertCaptchaPresence(TRUE);

    // Get CAPTCHA session ID and solution of the challenge.
    $captcha_sid = $this->getCaptchaSidFromForm();
    $captcha_token = $this->getCaptchaTokenFromForm();
    $solution = "Test 123";

    // Page settings to post, with correct CAPTCHA answer.
    $edit = $this->getNodeFormValues();
    $edit['captcha_response'] = $solution;
    // Preview the node.
    $this->drupalPostForm(NULL, $edit, t('Preview'));
    // Answer should be accepted.
    $this->assertCaptchaResponseAccepted();
    // Check that there is no CAPTCHA after preview.
    $this->assertCaptchaPresence(FALSE);

    // Post a new comment, reusing the previous CAPTCHA session.
    $edit = $this->getNodeFormValues();
    $edit['captcha_sid'] = $captcha_sid;
    $edit['captcha_token'] = $captcha_token;
    $edit['captcha_response'] = $solution;
    $this->drupalPostForm('node/add/page', $edit, t('Preview'));
    // CAPTCHA session reuse attack should be detected.
    $this->assertCaptchaSessionIdReuseAttackDetection();
    // There should be a CAPTCHA.
    $this->assertCaptchaPresence(TRUE);
  }

  /**
   * Test Captcha attack detection on login form.
   */
  public function testCaptchaSessionReuseAttackDetectionOnLoginForm() {
    // Set CAPTCHA on login form.
    captcha_set_form_id_setting('user_login_form', 'captcha/Test');
    $this->config('captcha.settings')
      ->set('persistence', CAPTCHA_PERSISTENCE_SKIP_ONCE_SUCCESSFUL_PER_FORM_INSTANCE)
      ->save();

    // Go to log in form.
    $this->drupalGet('<front>');
    $this->assertCaptchaPresence(TRUE);

    // Get CAPTCHA session ID and solution of the challenge.
    $captcha_sid = $this->getCaptchaSidFromForm();
    $captcha_token = $this->getCaptchaTokenFromForm();
    $solution = "Test 123";

    // Log in through form.
    $edit = [
      'name' => $this->normalUser->getDisplayName(),
      'pass' => $this->normalUser->pass_raw,
      'captcha_response' => $solution,
    ];
    $this->drupalPostForm(NULL, $edit, t('Log in'), [], [], self::LOGIN_HTML_FORM_ID);
    $this->assertCaptchaResponseAccepted();
    $this->assertCaptchaPresence(FALSE);
    // If a "log out" link appears on the page, it is almost certainly because
    // the login was successful.
    $this->assertText($this->normalUser->getDisplayName());

    // Log out again.
    $this->drupalLogout();

    // Try to log in again, reusing the previous CAPTCHA session.
    $edit += [
      'captcha_sid' => $captcha_sid,
      'captcha_token' => $captcha_token,
    ];
    $this->assert('pass', json_encode($edit));
    $this->drupalPostForm('<front>', $edit, t('Log in'));
    // CAPTCHA session reuse attack should be detected.
    $this->assertCaptchaSessionIdReuseAttackDetection();
    // There should be a CAPTCHA.
    $this->assertCaptchaPresence(TRUE);
  }

  /**
   * Test multiple captcha widgets on single page.
   */
  public function testMultipleCaptchaProtectedFormsOnOnePage() {
    \Drupal::service('module_installer')->install(['block']);
    $this->drupalPlaceBlock('user_login_block');
    // Set Test CAPTCHA on comment form and login block.
    captcha_set_form_id_setting(self::COMMENT_FORM_ID, 'captcha/Test');
    captcha_set_form_id_setting('user_login_form', 'captcha/Test');
    $this->allowCommentPostingForAnonymousVisitors();

    // Create a node with comments enabled.
    $node = $this->drupalCreateNode();

    // Preview comment with correct CAPTCHA answer.
    $edit = $this->getCommentFormValues();
    $comment_subject = $edit['subject[0][value]'];
    $edit['captcha_response'] = 'Test 123';
    $this->drupalPostForm('comment/reply/node/' . $node->id() . '/comment', $edit, t('Preview'));
    // Post should be accepted: no warnings,
    // no CAPTCHA reuse detection (which could be used by user log in block).
    $this->assertCaptchaResponseAccepted();
    $this->assertText($comment_subject);
  }

}
