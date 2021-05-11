<?php

namespace Drupal\Tests\captcha\Functional;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Base class for CAPTCHA tests.
 *
 * Provides common setup stuff and various helper functions.
 */
abstract class CaptchaWebTestBase extends BrowserTestBase {

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
   * Form ID of comment form on standard (page) node.
   */
  const COMMENT_FORM_ID = 'comment_comment_form';

  const LOGIN_HTML_FORM_ID = 'user-login-form';

  /**
   * Drupal path of the (general) CAPTCHA admin page.
   */
  const CAPTCHA_ADMIN_PATH = 'admin/config/people/captcha';

  /**
   * Modules to install for this Test class.
   *
   * @var array
   */
  protected static $modules = ['captcha', 'comment'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

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
    $this->assertSession()->pageTextNotContains(self::CAPTCHA_UNKNOWN_CSID_ERROR_MESSAGE,
      'CAPTCHA response should be accepted (known CSID).',
      'CAPTCHA'
    );
    // There should be no error message about wrong response.
    $this->assertSession()->pageTextNotContains(self::CAPTCHA_WRONG_RESPONSE_ERROR_MESSAGE,
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
      $this->assertSession()->pageTextContains(_captcha_get_description(),
        'There should be a CAPTCHA on the form.', 'CAPTCHA'
      );
    }
    else {
      $this->assertSession()->pageTextNotContains(_captcha_get_description(),
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

    $element = current($elements);
    $captcha_sid = (int) $element->getValue();

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
    $element = current($elements);
    $captcha_token = (int) $element->getValue();

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
    $this->assertTrue('pass', json_encode($elements));
    $challenge = (string) $elements[0];
    $this->assertTrue('pass', $challenge);
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

}
