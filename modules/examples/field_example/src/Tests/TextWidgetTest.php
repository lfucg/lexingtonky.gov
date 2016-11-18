<?php

namespace Drupal\field_example\Tests;

use Drupal\field_example\FieldExampleWebTestBase;

/**
 * Test basic functionality of the widgets.
 *
 * Create a content type with a example_field_rgb field, configure it with the
 * field_example_text-widget, create a node and check for correct values.
 *
 * @group field_example
 * @group examples
 *
 * @ingroup field_example
 */
class TextWidgetTest extends FieldExampleWebTestBase {

  /**
   * Test basic functionality of the example field.
   *
   * - Creates a content type.
   * - Adds a single-valued field_example_rgb to it.
   * - Adds a multivalued field_example_rgb to it.
   * - Creates a node of the new type.
   * - Populates the single-valued field.
   * - Populates the multivalued field with two items.
   * - Tests the result.
   */
  public function testSingleValueField() {
    // Add a single field as administrator user.
    $this->drupalLogin($this->administratorAccount);
    $this->fieldName = $this->createField('field_example_rgb', 'field_example_text', '1');

    // Now that we have a content type with the desired field, switch to the
    // author user to create content with it.
    $this->drupalLogin($this->authorAccount);
    $this->drupalGet('node/add/' . $this->contentTypeName);

    // Add a node.
    $title = $this->randomMachineName(20);
    $edit = array(
      'title[0][value]' => $title,
      'field_' . $this->fieldName . '[0][value]' => '#000001',
    );

    // Create the content.
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('@type @title has been created', array('@type' => $this->contentTypeName, '@title' => $title)));

    // Verify the value is shown when viewing this node.
    $field_p = $this->xpath("//div[contains(@class,'field--type-field-example-rgb')]/div/p");
    $this->assertEqual((string) $field_p[0], "The color code in this field is #000001");
  }

  /**
   * Test basic functionality of the example field.
   *
   * - Creates a content type.
   * - Adds a single-valued field_example_rgb to it.
   * - Adds a multivalued field_example_rgb to it.
   * - Creates a node of the new type.
   * - Populates the single-valued field.
   * - Populates the multivalued field with two items.
   * - Tests the result.
   */
  public function testMultiValueField() {
    // Add a single field as administrator user.
    $this->drupalLogin($this->administratorAccount);
    $this->fieldName = $this->createField('field_example_rgb', 'field_example_text', '-1');

    // Now that we have a content type with the desired field, switch to the
    // author user to create content with it.
    $this->drupalLogin($this->authorAccount);
    $this->drupalGet('node/add/' . $this->contentTypeName);

    // Add a node.
    $title = $this->randomMachineName(20);
    $edit = array(
      'title[0][value]' => $title,
      'field_' . $this->fieldName . '[0][value]' => '#00ff00',
    );

    // We want to add a 2nd item to the multivalue field, so hit "add another".
    $this->drupalPostForm(NULL, $edit, t('Add another item'));

    $edit = array(
      'field_' . $this->fieldName . '[1][value]' => '#ffffff',
    );

    // Now we can fill in the second item in the multivalue field and save.
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('@type @title has been created', array('@type' => $this->contentTypeName, '@title' => $title)));

    // Verify the value is shown when viewing this node.
    $field_p = $this->xpath("//div[contains(@class,'field--type-field-example-rgb')]/div/div/p");
    $this->assertEqual((string) $field_p[0], "The color code in this field is #00ff00");
    $this->assertEqual((string) $field_p[1], "The color code in this field is #ffffff");
  }

}
