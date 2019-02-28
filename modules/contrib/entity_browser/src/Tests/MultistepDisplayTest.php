<?php

namespace Drupal\entity_browser\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the multistep display selection display.
 *
 * @group entity_browser
 */
class MultistepDisplayTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_browser', 'block', 'node', 'file'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Tests multistep display.
   */
  public function testMultistepDisplay() {
    $account = $this->drupalCreateUser([
      'administer entity browsers',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/content/entity_browser');
    $this->clickLink('Add Entity browser');
    $edit = [
      'label' => 'Test entity browser',
      'name' => 'test_entity_browser',
      'display' => 'iframe',
      'widget_selector' => 'tabs',
      'selection_display' => 'multi_step_display',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    $this->assertUrl('/admin/config/content/entity_browser/test_entity_browser/widgets');

    $this->assertText('There are no widgets', 'Widget Settings displayed');

    $this->clickLink('General Settings');

    $this->assertURL('/admin/config/content/entity_browser/test_entity_browser/edit');

    $this->assertText('test_entity_browser', 'Machine Name is shown.');
    $this->assertText('Display plugin', 'Display plugin is shown.');
    $this->assertText('Widget selector plugin', 'Widget selector plugin is shown.');
    $this->assertText('Selection display plugin', 'Selection display plugin is shown.');

    $this->assertOptionSelected('edit-display', 'iframe', 'Iframe Selected');
    $this->assertOptionSelected('edit-widget-selector', 'tabs', 'Tabs Selected');
    $this->assertOptionSelected('edit-selection-display', 'multi_step_display', 'Multistep selection display Selected');

    $this->clickLink('Delete');

    $this->assertURL('/admin/config/content/entity_browser/test_entity_browser/delete');

    $this->assertText('This action cannot be undone.');

    $this->drupalPostForm(NULL, [], 'Delete Entity Browser');

    $this->assertUrl('admin/config/content/entity_browser');

    $this->assertText('Entity browser Test entity browser was deleted.', 'Confirmation message found.');

  }

}
