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

    // Login as admin,check the message list overview.
    $this->drupalLogin($admin_user);
    $this->drupalGet('admin/structure/contact/messages');
    $rows = $this->xpath('//tbody/tr');
    // Make sure only 1 message is available.
    $this->assertEqual(count($rows), 1);
    // Some fields should be present.
    $this->assertText('Test_subject');
    $this->assertText('Test_name');
    $this->assertText('test_label');

    // Make sure the stored message is correct.
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
    $this->assertText('Deleted contact message Test_subject.');
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
  }

}
