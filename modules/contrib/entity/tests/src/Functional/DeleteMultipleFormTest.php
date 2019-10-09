<?php

namespace Drupal\Tests\entity\Functional;

use Drupal\entity_module_test\Entity\EnhancedEntity;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the delete multiple confirmation form.
 *
 * @group entity
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DeleteMultipleFormTest extends BrowserTestBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_module_test', 'user', 'entity'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->account = $this->drupalCreateUser(['administer entity_test_enhanced']);
    $this->drupalLogin($this->account);
  }

  /**
   * Tests the add page.
   */
  public function testForm() {
    $entities = [];
    $selection = [];
    for ($i = 0; $i < 2; $i++) {
      $entity = EnhancedEntity::create([
        'type' => 'default',
      ]);
      $entity->save();
      $entities[$entity->id()] = $entity;

      $langcode = $entity->language()->getId();
      $selection[$entity->id()][$langcode] = $langcode;
    }
    // Add the selection to the tempstore just like DeleteAction would.
    $tempstore = \Drupal::service('tempstore.private')->get('entity_delete_multiple_confirm');
    $tempstore->set($this->account->id() . ':entity_test_enhanced', $selection);

    $this->drupalGet('/entity_test_enhanced/delete');
    $assert = $this->assertSession();
    $assert->statusCodeEquals(200);
    $assert->elementTextContains('css', '.page-title', 'Are you sure you want to delete these enhanced entities?');
    $delete_button = $this->getSession()->getPage()->findButton('Delete');
    $delete_button->click();
    $assert = $this->assertSession();
    $assert->addressEquals('/entity_test_enhanced');
    $assert->responseContains('Deleted 2 items.');

    \Drupal::entityTypeManager()->getStorage('entity_test_enhanced')->resetCache();
    $remaining_entities = EnhancedEntity::loadMultiple(array_keys($selection));
    $this->assertEmpty($remaining_entities);
  }

}
