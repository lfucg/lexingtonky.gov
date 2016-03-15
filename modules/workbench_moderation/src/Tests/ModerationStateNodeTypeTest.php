<?php
/**
 * @file
 * Contains \Drupal\workbench_moderation\Tests\ModerationStateNodeTypeTest.
 */

namespace Drupal\workbench_moderation\Tests;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\Role;

/**
 * Tests moderation state node type integration.
 *
 * @group workbench_moderation
 */
class ModerationStateNodeTypeTest extends ModerationStateTestBase {

  /**
   * A node type without moderation state disabled.
   */
  public function testNotModerated() {
    $this->drupalLogin($this->adminUser);
    $this->createContentTypeFromUI('Not moderated', 'not_moderated');
    $this->assertText('The content type Not moderated has been added.');
    $this->grantUserPermissionToCreateContentOfType($this->adminUser, 'not_moderated');
    $this->drupalGet('node/add/not_moderated');
    $this->assertRaw('Save as unpublished');
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Test',
    ], t('Save and publish'));
    $this->assertText('Not moderated Test has been created.');
  }

  /**
   * Tests enabling moderation on an existing node-type, with content.
   */
  /**
   * A node type without moderation state enabled.
   */
  public function testEnablingOnExistingContent() {
    $this->drupalLogin($this->adminUser);
    $this->createContentTypeFromUI('Not moderated', 'not_moderated');
    $this->grantUserPermissionToCreateContentOfType($this->adminUser, 'not_moderated');
    $this->drupalGet('node/add/not_moderated');
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Test',
    ], t('Save and publish'));
    $this->assertText('Not moderated Test has been created.');
    // Now enable moderation state.
    $this->drupalGet('admin/structure/types/manage/not_moderated/moderation');
    $this->drupalPostForm(NULL, [
      'enable_moderation_state' => 1,
      'allowed_moderation_states[draft]' => 1,
      'allowed_moderation_states[needs_review]' => 1,
      'allowed_moderation_states[published]' => 1,
      'default_moderation_state' => 'draft',
    ], t('Save'));
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'title' => 'Test'
    ]);
    if (empty($nodes)) {
      $this->fail('Could not load node with title Test');
      return;
    }
    $node = reset($nodes);
    $this->drupalGet('node/' . $node->id());
    $this->assertResponse(200);
    $this->assertLinkByHref('node/' . $node->id() . '/edit');
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertResponse(200);
    $this->assertRaw('Save and Create New Draft');
    $this->assertNoRaw('Save and publish');
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

    if ($moderated) {
      $this->drupalGet('admin/structure/types/' . $content_type_id . '/moderation');
      $this->assertFieldByName('enable_moderation_state');
      $this->assertNoFieldChecked('edit-enable-moderation-state');
      $edit['enable_moderation_state'] = 1;
      foreach ($allowed_states as $state) {
        $edit['allowed_moderation_states[' . $state . ']'] = 1;
      }
      $edit['default_moderation_state'] = $default_state;

      $this->drupalPostForm('admin/structure/types/' . $content_type_id . '/moderation', $edit, t('Save'));
    }
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
    $role->save();
  }

}
