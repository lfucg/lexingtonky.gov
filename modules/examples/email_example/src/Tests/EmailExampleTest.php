<?php

namespace Drupal\email_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for the email_example module.
 *
 * @ingroup email_example
 *
 * @group email_example
 * @group examples
 */
class EmailExampleTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('email_example');

  /**
   * The installation profile to use with this test.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Email example functionality',
      'description' => 'Ensure the email example module is working.',
      'group' => 'Examples',
    );
  }

  /**
   * Test our new email form.
   *
   * Tests for the following:
   *
   * - A link to the email_example in the Tools menu.
   * - That you can successfully access the email_example page.
   */
  public function testEmailExampleBasic() {
    // Test for a link to the email_example in the Tools menu.
    $this->drupalGet('');
    $this->assertResponse(200, 'The Home page is available.');
    $this->assertLinkByHref('examples/email-example');

    // Verify if we can successfully access the email_example page.
    $this->drupalGet('examples/email-example');
    $this->assertResponse(200, 'The Email Example description page is available.');

    // Verifiy email form has email & message fields.
    $this->assertFieldById('edit-email', NULL, 'The email field appears.');
    $this->assertFieldById('edit-message', NULL, 'The message field appears.');

    // Verifiy email form is submitted.
    $edit = array('email' => 'example@example.com', 'message' => 'test');
    $this->drupalPostForm('examples/email-example', $edit, t('Submit'));
    $this->assertResponse(200);

    // Verifiy comfirmation page.
    $this->assertText(t('Your message has been sent.'), 'The text "Your message has been sent." appears on the email example page.', 'Form response with the right message.');
    $this->assertMailString('to', $edit['email'], 1);

    // Verifiy correct email recieved.
    $from = \Drupal::config('system.site')->get('mail');
    $t_options = array('langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId());
    $this->assertMailString('subject', t('E-mail sent from @site-name', array('@site-name' => $from), $t_options), 1);
    $this->assertMailString('body', $edit['message'], 1);
    $this->assertMailString('body', t("\n--\nMail altered by email_example module.", array(), $t_options), 1);
  }

}
