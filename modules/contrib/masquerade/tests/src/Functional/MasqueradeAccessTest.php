<?php

namespace Drupal\Tests\masquerade\Functional;

use Drupal\Core\Session\AccountInterface;

/**
 * Tests masquerade access mechanism.
 *
 * @todo Convert into DUTB. This is essentially a unit test for
 *   masquerade_target_user_access() only.
 *
 * @group masquerade
 */
class MasqueradeAccessTest extends MasqueradeWebTestBase {

  /**
   * Tests masquerade access for different source and target users.
   *
   * Test plan summary:
   * - root » admin
   * - admin » root
   * - admin » moderator (more roles but less privileges)
   * - admin » super (administrator and editor roles)
   * - admin » lead (editor roles)
   * - admin » masquerade (different role)
   * - admin » auth (less roles)
   * - moderator ! root
   * - moderator ! admin (less roles but more privileges)
   * - moderator ! editor (different roles + privileges)
   * - moderator » super (administrator and editor roles)
   * - moderator » lead (editor roles)
   * - moderator » auth
   * - [editor is access-logic-wise equal to moderator, so skipped]
   * - masquerade ! root
   * - masquerade ! admin (different role with more privileges)
   * - masquerade ! moderator (more roles)
   * - masquerade ! lead (editor roles)
   * - masquerade ! super (administrator and editor roles)
   * - masquerade » auth
   * - masquerade ! masquerade (self)
   * - lead ! root
   * - lead ! admin (different role with more privileges)
   * - lead ! moderator (more roles)
   * - lead ! super (administrator and editor roles)
   * - lead » editor
   * - lead » auth
   * - auth ! *
   */
  public function testAccess() {
    $this->drupalLogin($this->rootUser);
    $this->assertCanMasqueradeAs($this->admin_user);

    $this->drupalLogin($this->admin_user);
    // Permission 'masquerade as super user' granted by default.
    $this->assertCanMasqueradeAs($this->rootUser);
    // Permission 'masquerade as any user' granted by default.
    $this->assertCanMasqueradeAs($this->moderator_user);
    $this->assertCanMasqueradeAs($this->superUser);
    $this->assertCanMasqueradeAs($this->leadEditorUser);
    $this->assertCanMasqueradeAs($this->editor_user);
    $this->assertCanMasqueradeAs($this->masquerade_user);
    $this->assertCanMasqueradeAs($this->auth_user);

    // Test 'masquerade as any user' permission except UID 1.
    $this->drupalLogin($this->moderator_user);
    $this->assertCanNotMasqueradeAs($this->rootUser);
    $this->assertCanMasqueradeAs($this->admin_user);
    $this->assertCanMasqueradeAs($this->superUser);
    $this->assertCanMasqueradeAs($this->leadEditorUser);
    $this->assertCanMasqueradeAs($this->editor_user);
    $this->assertCanMasqueradeAs($this->masquerade_user);
    $this->assertCanMasqueradeAs($this->auth_user);

    // Test 'masquerade as @role' permission.
    $this->drupalLogin($this->editor_user);
    $this->assertCanNotMasqueradeAs($this->rootUser);
    $this->assertCanNotMasqueradeAs($this->admin_user);
    $this->assertCanNotMasqueradeAs($this->moderator_user);
    $this->assertCanNotMasqueradeAs($this->superUser);
    $this->assertCanNotMasqueradeAs($this->leadEditorUser);
    $this->assertCanMasqueradeAs($this->masquerade_user);
    $this->assertCanMasqueradeAs($this->auth_user);

    // Test 'masquerade as @role' permission.
    $this->drupalLogin($this->leadEditorUser);
    $this->assertCanNotMasqueradeAs($this->rootUser);
    $this->assertCanNotMasqueradeAs($this->admin_user);
    $this->assertCanNotMasqueradeAs($this->moderator_user);
    $this->assertCanNotMasqueradeAs($this->superUser);
    $this->assertCanNotMasqueradeAs($this->masquerade_user);
    $this->assertCanMasqueradeAs($this->editor_user);
    $this->assertCanMasqueradeAs($this->auth_user);

    // Test 'masquerade as authenticated' permission.
    $this->drupalLogin($this->masquerade_user);
    $this->assertCanNotMasqueradeAs($this->rootUser);
    $this->assertCanNotMasqueradeAs($this->admin_user);
    $this->assertCanNotMasqueradeAs($this->moderator_user);
    $this->assertCanNotMasqueradeAs($this->superUser);
    $this->assertCanNotMasqueradeAs($this->leadEditorUser);
    $this->assertCanNotMasqueradeAs($this->editor_user);
    $this->assertCanMasqueradeAs($this->auth_user);

    // Verify that a user cannot masquerade as himself.
    $edit = [
      'masquerade_as' => $this->masquerade_user->getAccountName(),
    ];
    $this->drupalPostForm('masquerade', $edit, $this->t('Switch'));
    $this->assertSession()
      ->responseContains($this->t('You cannot masquerade as yourself. Please choose a different user to masquerade as.'));
    $this->assertSession()->pageTextNotContains($this->t('Unmasquerade'));

    // Basic 'masquerade' permission check.
    $this->drupalLogin($this->auth_user);
    $this->drupalGet('masquerade');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Asserts that the logged-in user can masquerade as a given target user.
   *
   * @param \Drupal\Core\Session\AccountInterface $target_account
   *   The user to masquerade to.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function assertCanMasqueradeAs(AccountInterface $target_account) {
    $edit = [
      'masquerade_as' => $target_account->getAccountName(),
    ];
    $this->drupalPostForm('masquerade', $edit, $this->t('Switch'));
    $this->assertSession()
      ->responseNotContains($this->t('You are not allowed to masquerade as %name.', [
        '%name' => $target_account->getDisplayName(),
      ]));
    $this->clickLink($this->t('Unmasquerade'));
  }

  /**
   * Asserts that the logged-in user can not masquerade as a given target user.
   *
   * @param \Drupal\Core\Session\AccountInterface $target_account
   *   The user to masquerade to.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function assertCanNotMasqueradeAs(AccountInterface $target_account) {
    $edit = [
      'masquerade_as' => $target_account->getAccountName(),
    ];
    $this->drupalPostForm('masquerade', $edit, $this->t('Switch'));
    $this->assertSession()
      ->responseContains($this->t('You are not allowed to masquerade as %name.', [
        '%name' => $target_account->getDisplayName(),
      ]));
    $this->assertSession()->pageTextNotContains($this->t('Unmasquerade'));
  }

}
