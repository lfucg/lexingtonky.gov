<?php

namespace Drupal\field_example;

use Drupal\examples\Tests\ExamplesTestBase;

/**
 * Class FieldExampleWebTestBase.
 */
class FieldExampleWebTestBase extends ExamplesTestBase {

  /**
   * The content type name.
   *
   * @var string
   */
  protected $contentTypeName;

  /**
   * The administrator account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $administratorAccount;

  /**
   * The author account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $authorAccount;

  /**
   * The field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block', 'node', 'field_ui', 'field_example');

  /**
   * {@inheritdoc}
   *
   * Once installed, a content type with the desired field is created.
   */
  protected function setUp() {
    // Install Drupal.
    parent::setUp();

    // Create and login a user that creates the content type.
    $permissions = array(
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
    );
    $this->administratorAccount = $this->drupalCreateUser($permissions);
    parent::drupalLogin($this->administratorAccount);

    // Prepare a new content type where the field will be added.
    $this->contentTypeName = strtolower($this->randomMachineName(10));
    $this->drupalGet('admin/structure/types/add');
    $edit = array(
      'name' => $this->contentTypeName,
      'type' => $this->contentTypeName,
    );
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->assertText(t('The content type @name has been added.', array('@name' => $this->contentTypeName)));

    // Reset the permission cache.
    $create_permission = 'create ' . $this->contentTypeName . ' content';
    $this->checkPermissions(array($create_permission), TRUE);

    // Now that we have a new content type, create a user that has privileges
    // on the content type.
    $this->authorAccount = $this->drupalCreateUser(array($create_permission));
  }

  /**
   * Create a field on the content type created during setUp().
   *
   * @param string $type
   *   The storage field type to create.
   * @param string $widget_type
   *   The widget to use when editing this field.
   * @param int|string $cardinality
   *   Cardinality of the field. Use -1 to signify 'unlimited'.
   * @param string $fieldFormatter
   *   The formatter to use when editing this field.
   *
   * @return string
   *   Name of the field, like field_something
   */
  protected function createField($type = 'field_example_rgb', $widget_type = 'field_example_text', $cardinality = '1', $fieldFormatter = 'field_example_simple_text') {
    $this->drupalGet('admin/structure/types/manage/' . $this->contentTypeName . '/fields');

    // Go to the 'Add field' page.
    $this->clickLink('Add field');

    // Make a name for this field.
    $field_name = strtolower($this->randomMachineName(10));

    // Fill out the field form.
    $edit = array(
      'new_storage_type' => $type,
      'field_name' => $field_name,
      'label' => $field_name,
    );
    $this->drupalPostForm(NULL, $edit, t('Save and continue'));

    // Fill out the $cardinality form as if we're not using an unlimited number
    // of values.
    $edit = array(
      'cardinality' => 'number',
      'cardinality_number' => (string) $cardinality,
    );
    // If we have -1 for $cardinality, we should change the form's drop-down
    // from 'Number' to 'Unlimited'.
    if (-1 == $cardinality) {
      $edit = array(
        'cardinality' => '-1',
        'cardinality_number' => '1',
      );
    }

    // And now we save the cardinality settings.
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));
    debug(
      t('Saved settings for field %field_name with widget %widget_type and cardinality %cardinality',
        array(
          '%field_name' => $field_name,
          '%widget_type' => $widget_type,
          '%cardinality' => $cardinality,
        )
      )
    );
    $this->assertText(t('Updated field @name field settings.', array('@name' => $field_name)));

    // Set the widget type for the newly created field.
    $this->drupalGet('admin/structure/types/manage/' . $this->contentTypeName . '/form-display');
    $edit = array(
      'fields[field_' . $field_name . '][type]' => $widget_type,
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Set the field formatter for the newly created field.
    $this->drupalGet('admin/structure/types/manage/' . $this->contentTypeName . '/display');
    $edit1 = array(
      'fields[field_' . $field_name . '][type]' => $fieldFormatter,
    );
    $this->drupalPostForm(NULL, $edit1, t('Save'));

    return $field_name;
  }

}
