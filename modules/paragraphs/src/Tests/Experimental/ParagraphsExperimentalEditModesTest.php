<?php

namespace Drupal\paragraphs\Tests\Experimental;

use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests paragraphs edit modes.
 *
 * @group paragraphs
 */
class ParagraphsExperimentalEditModesTest extends ParagraphsExperimentalTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'image',
  ];

  /**
   * Tests the collapsed summary of paragraphs.
   */
  public function testCollapsedSummary() {
    $this->addParagraphedContentType('paragraphed_test', 'field_paragraphs');
    $this->loginAsAdmin(['create paragraphed_test content', 'edit any paragraphed_test content']);

    // Add a Paragraph type.
    $paragraph_type = 'image_text_paragraph';
    $this->addParagraphsType($paragraph_type);
    $this->addParagraphsType('text');
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'image', 'Image', 'image', [], ['settings[alt_field_required]' => FALSE]);
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'text', 'Text', 'text_long', [], []);

    // Add a user Paragraph Type
    $paragraph_type = 'user_paragraph';
    $this->addParagraphsType($paragraph_type);
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'user', 'User', 'entity_reference', ['settings[target_type]' => 'user'], []);

    // Set edit mode to closed.
    $this->drupalGet('admin/structure/types/manage/paragraphed_test/form-display');
    $this->drupalPostAjaxForm(NULL, [], "field_paragraphs_settings_edit");
    $edit = ['fields[field_paragraphs][settings_edit_form][settings][edit_mode]' => 'closed'];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Add a paragraph.
    $this->drupalPostAjaxForm('node/add/paragraphed_test', [], 'field_paragraphs_image_text_paragraph_add_more');

    $text = 'Trust me I am an image';
    file_put_contents('temporary://myImage1.jpg', $text);

    // Create a node with an image and text.
    $edit = [
      'title[0][value]' => 'Test article',
      'field_paragraphs[0][subform][field_text][0][value]' => 'text_summary',
      'files[field_paragraphs_0_subform_field_image_0]' => drupal_realpath('temporary://myImage1.jpg'),
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $this->clickLink(t('Edit'));
    $this->drupalPostForm(NULL, [], t('Add user_paragraph'));
    $edit = [
      'field_paragraphs[1][subform][field_user][0][target_id]' => $this->admin_user->label() . ' (' . $this->admin_user->id() . ')',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Assert the summary is correctly generated.
    $this->clickLink(t('Edit'));
    $this->assertRaw('<div class="paragraphs-collapsed-description">myImage1.jpg, text_summary');
    $this->assertRaw('<div class="paragraphs-collapsed-description">' . $this->admin_user->label());

    // Edit and remove alternative text.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
    $edit = [
      'field_paragraphs[0][subform][field_image][0][alt]' => 'alternative_text_summary',
      'field_paragraphs[0][subform][field_image][0][width]' => 300,
      'field_paragraphs[0][subform][field_image][0][height]' => 300,
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'field_paragraphs_0_collapse');
    // Assert the summary is correctly generated.
    $this->assertRaw('<div class="paragraphs-collapsed-description">alternative_text_summary, text_summary');

    // Remove image.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_subform_field_image_0_remove_button');
    $this->drupalPostForm(NULL, [], t('Save and keep published'));

    // Assert the summary is correctly generated.
    $this->clickLink(t('Edit'));
    $this->assertRaw('<div class="paragraphs-collapsed-description">text_summary');

    $this->addParagraphsType('nested_paragraph');
    static::fieldUIAddNewField('admin/structure/paragraphs_type/nested_paragraph', 'nested_content', 'Nested Content', 'entity_reference_revisions', ['settings[target_type]' => 'paragraph'], []);
    $this->drupalGet('admin/structure/paragraphs_type/nested_paragraph/form-display');
    $this->drupalPostForm(NULL, ['fields[field_nested_content][type]' => 'entity_reference_paragraphs'], t('Save'));

    $test_user = $this->drupalCreateUser([]);

    $this->drupalGet('node/add/paragraphed_test');
    $this->drupalPostForm(NULL, NULL, t('Add nested_paragraph'));
    $this->drupalPostAjaxForm(NULL, NULL, t('field_paragraphs_0_subform_field_nested_content_user_paragraph_add_more'));
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Node title',
      'field_paragraphs[0][subform][field_nested_content][0][subform][field_user][0][target_id]' => $test_user->label() . ' (' . $test_user->id() . ')',
    ], t('Save and publish'));

    // Create an orphaned ER field item by deleting the target entity.
    $test_user->delete();

    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['title' => 'Node title']);
    $this->drupalGet('node/' . current($nodes)->id() . '/edit');
    $this->drupalPostAjaxForm(NULL, [], t('field_paragraphs_0_edit'));
    $this->drupalPostAjaxForm(NULL, [], t('field_paragraphs_0_collapse'));
    $this->assertResponse(200);
  }

}
