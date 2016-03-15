<?php

/**
 * @file
 * Contains \Drupal\workbench_moderation\Tests\ModerationStateNodeTest.
 */

namespace Drupal\workbench_moderation\Tests;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Tests permission access control around nodes.
 *
 * @group workbench_moderation
 */
class NodeAccessTest extends ModerationStateTestBase {

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
   * Verifies that a non-admin user can still access the appropriate pages.
   */
  public function testPageAccess() {
    $this->drupalLogin($this->adminUser);

    // Create a node to test with.
    $this->drupalPostForm('node/add/moderated_content', [
      'title[0][value]' => 'moderated content',
    ], t('Save and Create New Draft'));
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => 'moderated content',
      ]);

    if (!$nodes) {
      $this->fail('Test node was not saved correctly.');
      return;
    }

    /** @var NodeInterface $node */
    $node = reset($nodes);

    $view_path = 'node/' . $node->id();
    $edit_path = 'node/' . $node->id() . '/edit';
    $latest_path = 'node/' . $node->id() . '/latest';

    // Set up needs review revision.
    $this->drupalPostForm($edit_path, [], t('Save and Request Review'));

    // Now make a new user and verify that the new user's access is correct.
    $user = $this->createUser([
      'use draft_draft transition',
      'use draft_needs_review transition',
      'use published_draft transition',
      'use needs_review_published transition',
      'view any unpublished content',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet($edit_path);
    $this->assertResponse(403);

    $this->drupalGet($latest_path);
    $this->assertResponse(200);
    $this->drupalGet($view_path);
    $this->assertResponse(200);

    // Now make another user, who should not be able to see forward revisions.
    $user = $this->createUser([
      'use draft_needs_review transition',
      'use published_draft transition',
      'use needs_review_published transition',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet($edit_path);
    $this->assertResponse(403);

    $this->drupalGet($latest_path);
    $this->assertResponse(403);
    $this->drupalGet($view_path);
    $this->assertResponse(403);
  }
}
