<?php

/**
 * @file
 * Contains \Drupal\contact_storage\Tests\ContactStorageTest.
 */

namespace Drupal\contact_storage\Tests;

/**
 * Tests storing contact messages and viewing them through UI.
 *
 * @group contact_storage
 */
class ContactStorageTest extends ContactStorageTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'text',
    'contact',
    'field_ui',
    'contact_storage_test',
    'contact_test',
    'contact_storage',
  );

  /**
   * Tests contact messages submitted through contact form.
   */
  public function testContactStorage() {
    // Create and login administrative user.
    $admin_user = $this->drupalCreateUser(array(
      'access site-wide contact form',
      'administer contact forms',
      'administer users',
      'administer account settings',
      'administer contact_message fields',
      'administer contact_message display',
    ));
    $this->drupalLogin($admin_user);
    // Create first valid contact form.
    $mail = 'simpletest@example.com';
    $this->addContactForm('test_id', 'test_label', $mail, '', TRUE);
    $this->assertText(t('Contact form test_label has been added.'));

    // Ensure that anonymous can submit site-wide contact form.
    user_role_grant_permissions(DRUPAL_ANONYMOUS_RID, array('access site-wide contact form'));
    $this->drupalLogout();
    $this->drupalGet('contact');
    $this->assertText(t('Your email address'));
    $this->assertNoText(t('Form'));
    $this->submitContact('Test_name', $mail, 'Test_subject', 'test_id', 'Test_message');
    $this->assertText(t('Your message has been sent.'));

    // Login as admin.
    $this->drupalLogin($admin_user);

    $display_fields = array(
      "The sender's name",
      "The sender's email",
      "Subject"
    );

    // Check that name, subject and mail are configurable on display.
    $this->drupalGet('admin/structure/contact/manage/test_id/display');
    foreach ($display_fields as $label) {
      $this->assertText($label);
    }

    // Check the message list overview.
    $this->drupalGet('admin/structure/contact/messages');
    $rows = $this->xpath('//tbody/tr');
    // Make sure only 1 message is available.
    $this->assertEqual(count($rows), 1);
    // Some fields should be present.
    $this->assertText('Test_subject');
    $this->assertText('Test_name');
    $this->assertText('test_label');

    // Click the view link and make sure name, subject and email are displayed
    // by default.
    $this->clickLink(t('View'));
    foreach ($display_fields as $label) {
      $this->assertText($label);
    }

    // Make sure the stored message is correct.
    $this->drupalGet('admin/structure/contact/messages');
    $this->clickLink(t('Edit'));
    $this->assertFieldById('edit-name', 'Test_name');
    $this->assertFieldById('edit-mail', $mail);
    $this->assertFieldById('edit-subject-0-value', 'Test_subject');
    $this->assertFieldById('edit-message-0-value', 'Test_message');
    // Submit should redirect back to listing.
    $this->drupalPostForm(NULL, array(), t('Save'));
    $this->assertUrl('admin/structure/contact/messages');

    // Delete the message.
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertRaw(t('The @entity-type %label has been deleted.', [
      // See \Drupal\Core\Entity\EntityDeleteFormTrait::getDeletionMessage().
      '@entity-type' => 'contact message',
      '%label'       => 'Test_subject',
    ]));
    // Make sure no messages are available.
    $this->assertText('There is no Contact message yet.');

    // Fill the redirect field and assert the page is successfully redirected.
    $edit = ['contact_storage_uri' => 'entity:user/' . $admin_user->id()];
    $this->drupalPostForm('admin/structure/contact/manage/test_id', $edit, t('Save'));
    $edit = [
      'subject[0][value]' => 'Test subject',
      'message[0][value]' => 'Test message',
    ];
    $this->drupalPostForm('contact', $edit, t('Send message'));
    $this->assertText('Your message has been sent.');
    $this->assertEqual($this->url, $admin_user->urlInfo()->setAbsolute()->toString());

    // Fill the "Submit button text" field and assert the form can still be
    // submitted.
    $edit = [
      'contact_storage_submit_text' => 'Submit the form',
      'contact_storage_preview' => FALSE,
    ];
    $this->drupalPostForm('admin/structure/contact/manage/test_id', $edit, t('Save'));
    $edit = [
      'subject[0][value]' => 'Test subject',
      'message[0][value]' => 'Test message',
    ];
    $this->drupalGet('contact');
    $element = $this->cssSelect('#edit-preview');
    // Preview button is hidden.
    $this->assertTrue(empty($element));
    $this->drupalPostForm(NULL, $edit, t('Submit the form'));
    $this->assertText('Your message has been sent.');
  }

}
