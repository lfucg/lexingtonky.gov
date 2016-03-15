<?php

/**
 * @file
 * Contains \Drupal\workbench_moderation\Tests\ModerationFormTest.
 */

namespace Drupal\workbench_moderation\Tests;

use Drupal\Core\Url;

/**
 * Tests the moderation form, specifically on nodes.
 *
 * @group workbench_moderation
 */
class ModerationFormTest extends ModerationStateTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
    $this->createContentTypeFromUI('Moderated content', 'moderated_content', TRUE, [
      'draft',
      'needs_review',
      'published'
    ], 'draft');
    $this->grantUserPermissionToCreateContentOfType($this->adminUser, 'moderated_content');
  }

  /**
   * Tests the moderation form.
   */
  public function testModerationForm() {
    // Create new moderated content in draft.
    $this->drupalPostForm('node/add/moderated_content', [
      'title[0][value]' => 'Some moderated content',
      'body[0][value]' => 'First version of the content.',
    ], t('Save and Create New Draft'));

    $node = $this->drupalGetNodeByTitle('Some moderated content');
    $canonical_path = sprintf('node/%d', $node->id());
    $edit_path = sprintf('node/%d/edit', $node->id());
    $latest_version_path = sprintf('node/%d/latest', $node->id());

    $this->assertTrue($this->adminUser->hasPermission('edit any moderated_content content'));

    // For the first revision, the latest-version tab doesn't show so don't
    // bother checking for it.

    // Make a new forward revision; after saving, the tab and form should show.
    $this->drupalPostForm($edit_path, [
      'body[0][value]' => 'Second version of the content.',
    ], t('Save and Request Review'));
    $this->drupalGet($latest_version_path);
    $this->assertResponse(200);
    $this->assertText('Second version of the content.');
    $this->assertText('Status', 'Form text found on the latest-version page.');
    $this->assertText('Needs Review', 'Correct status found on the latest-version page.');

    // Make a new published revision; after saving, the latest-version tab should
    // be unavailable and the public node page should have no form on it.
    $this->drupalPostForm($edit_path, [
      'body[0][value]' => 'Third version of the content.',
    ], t('Save and Publish'));
    $this->drupalGet($canonical_path);
    $this->assertResponse(200);
    $this->assertNoText('Current status', 'The node view page has no moderation form.');
    $this->drupalGet($latest_version_path);
    $this->assertResponse(403);

    // Make a new forward revision; after saving, the latest-version tab should
    // be back, and have a form, while the node view page still has no form.
    $this->drupalPostForm($edit_path, [
      'body[0][value]' => 'Fourth version of the content.',
    ], t('Save and Create New Draft'));
    $this->drupalGet($latest_version_path);
    $this->assertResponse(200);
    $this->assertText('Status', 'Form text found on the latest-version page.');
    $this->assertText('Draft', 'Correct status found on the latest-version page.');
    $this->drupalGet($canonical_path);
    $this->assertResponse(200);
    $this->assertNoText('Current status', 'The node view page has no moderation form.');

    // Submit the moderation form to change status.
    $this->drupalPostForm($latest_version_path, [
      'new_state' => 'needs_review',
    ], t('Apply'));
    $this->drupalGet($latest_version_path);
    $this->assertResponse(200);
    $this->assertText('Status', 'Form text found on the latest-version page.');
    $this->assertText('Needs Review', 'Correct status found on the latest-version page.');
  }

}
