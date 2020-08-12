<?php

namespace Drupal\Tests\workbench_moderation\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\workbench_moderation\Traits\WorkbenchModerationTestTrait;

/**
 * Tests access to the moderation form.
 *
 * @group workbench_moderation
 */
class ModerationFormAccessTest extends BrowserTestBase {

  use WorkbenchModerationTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'workbench_moderation',
    'node',
    'options',
    'user',
    'system',
  ];

  /**
   * Tests user access to the moderation form.
   */
  public function testModerationFormAccess() {
    $page = $this->getSession()->getPage();

    $base_permissions = [
      'access content',
      'view all revisions',
      'view moderation states',
      'view latest version',
      'view any unpublished content',
      'use draft_needs_review transition',
    ];

    $node_type = $this->createNodeType('Test', 'test');
    $entity_display = EntityViewDisplay::load('node.test.default');
    $entity_display->setComponent('workbench_moderation_control');
    $entity_display->save();

    // Create a node with a forward revision for the form to display on.
    $node = $this->createNode(['type' => $node_type->id(), 'moderation_state' => 'published']);
    $node->moderation_state->target_id = 'draft';
    $node->save();

    // Page doesn't have form if user can't edit or bypass edit access.
    $this->drupalLogin($this->drupalCreateUser($base_permissions));
    $this->drupalGet($node->toUrl('latest-version'));
    $this->assertFalse($page->hasSelect('Moderate'));

    // Page has moderation form for user that can edit.
    $this->drupalLogin($this->drupalCreateUser(array_merge($base_permissions, ['edit any test content'])));
    $this->drupalGet($node->toUrl('latest-version'));
    $this->assertTrue($page->hasSelect('Moderate'));

    // Page has moderation form for user that has edit bypass permission.
    $this->drupalLogin($this->drupalCreateUser(array_merge($base_permissions, ['moderate entities that cannot edit'])));
    $this->drupalGet($node->toUrl('latest-version'));
    $this->assertTrue($page->hasSelect('Moderate'));
  }

}
