<?php
/**
 * @file
 * Contains \Drupal\workbench_moderation\Tests\ModerationStateTestBase.
 */

namespace Drupal\workbench_moderation\Tests;

use Drupal\Core\Session\AccountInterface;
use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\Role;

/**
 * Defines a base class for moderation state tests.
 */
abstract class ModerationStateTestBase extends WebTestBase {

  /**
   * Profile to use.
   */
  protected $profile = 'testing';

  /**
   * Admin user
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'administer moderation states',
    'administer moderation state transitions',
    'use draft_draft transition',
    'use draft_needs_review transition',
    'use published_draft transition',
    'use needs_review_published transition',
    'access administration pages',
    'administer content types',
    'administer nodes',
    'view own unpublished content',
  ];

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'workbench_moderation',
    'block',
    'block_content',
    'node',
    'views',
    'options',
    'user',
  ];

  /**
   * Sets the test up.
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalPlaceBlock('local_tasks_block', ['id' => 'tabs_block']);
    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_actions_block', ['id' => 'actions_block']);
  }

  /**
   * Creates a content-type from the UI.
   *
   * @param string $content_type_name
   *   Content type human name.
   * @param string $content_type_id
   *   Machine name.
   * @param bool $moderated
   *   TRUE if should be moderated
   * @param string[] $allowed_states
   *   Array of allowed state IDs
   * @param string $default_state
   *   Default state.
   */
  protected function createContentTypeFromUI($content_type_name, $content_type_id, $moderated = FALSE, $allowed_states = [], $default_state = NULL) {
    $this->drupalGet('admin/structure/types');
    $this->clickLink('Add content type');
    $edit = [
      'name' => $content_type_name,
      'type' => $content_type_id,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save content type'));

    $this->drupalGet('admin/structure/types/manage/' . $content_type_id . '/moderation');
    $this->assertFieldByName('enable_moderation_state');
    $this->assertNoFieldChecked('edit-enable-moderation-state');
    $edit = [];
    if ($moderated) {
      $edit['enable_moderation_state'] = 1;
      foreach ($allowed_states as $state) {
        $edit['allowed_moderation_states[' . $state . ']'] = 1;
      }
      $edit['default_moderation_state'] = $default_state;
    }
    $this->drupalPostForm(NULL, $edit, t('Save'));
  }

  /**
   * Grants given user permission to create content of given type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User to grant permission to.
   * @param string $content_type_id
   *   Content type ID.
   */
  protected function grantUserPermissionToCreateContentOfType(AccountInterface $account, $content_type_id) {
    $role_ids = $account->getRoles(TRUE);
    /* @var \Drupal\user\RoleInterface $role */
    $role_id = reset($role_ids);
    $role = Role::load($role_id);
    $role->grantPermission(sprintf('create %s content', $content_type_id));
    $role->grantPermission(sprintf('edit any %s content', $content_type_id));
    $role->grantPermission(sprintf('delete any %s content', $content_type_id));
    $role->save();
  }

}
