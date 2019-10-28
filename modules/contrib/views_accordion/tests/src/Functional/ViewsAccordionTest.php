<?php

namespace Drupal\Tests\views_accordion\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for the Views Accordion module.
 *
 * @group views_accordion
 */
class ViewsAccordionTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views_accordion_test',
    'views_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer views',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Tests Views Accordion functionality.
   */
  public function testViewsAccordion() {
    $assert_session = $this->assertSession();
    // Test views add form.
    $edit = [
      'id' => 'test',
      'label' => 'test',
      'show[wizard_key]' => 'node',
      'show[sort]' => 'none',
      'page[create]' => 1,
      'page[title]' => 'Test',
      'page[path]' => 'test',
      'page[style][style_plugin]' => 'views_accordion',
      'page[style][row_plugin]' => 'teasers',
    ];
    $this->drupalPostForm('admin/structure/views/add', $edit, 'Save and edit');
    $assert_session->pageTextContains('Views accordion requires Fields as row style');

    $edit['page[style][row_plugin]'] = 'fields';
    $this->drupalPostForm('admin/structure/views/add', $edit, 'Save and edit');
    $assert_session->pageTextContains('The view test has been saved.');

    // Assert the options of our exported view display correctly.
    $this->drupalGet('admin/structure/views/view/views_accordion_test/edit');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains('jQuery UI accordion');

    // Verify the style options show with the right values in the form.
    $this->drupalGet('admin/structure/views/nojs/display/views_accordion_test/page_1/style_options');
    $assert_session->statusCodeEquals(200);
    $assert_session->checkboxNotChecked('style_options[grouping][0][use-grouping-header]');
    $assert_session->checkboxNotChecked('style_options[disableifone]');
    $assert_session->checkboxNotChecked('style_options[collapsible]');
    $assert_session->fieldValueEquals('style_options[animated]', 'none');
    $assert_session->fieldValueEquals('style_options[animation_time]', '300');
    $assert_session->fieldValueEquals('style_options[heightStyle]', 'auto');
    $assert_session->fieldValueEquals('style_options[event]', 'click');
    $assert_session->checkboxChecked('style_options[use_header_icons]');
    $assert_session->fieldValueEquals('style_options[icon_header]', 'ui-icon-triangle-1-e');
    $assert_session->fieldValueEquals('style_options[icon_active_header]', 'ui-icon-triangle-1-s');
  }

}
