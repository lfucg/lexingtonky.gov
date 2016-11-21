<?php

namespace Drupal\config_entity_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the Config Entity Example module.
 *
 * @group config_entity_example
 * @group examples
 *
 * @ingroup config_entity_example
 */
class ConfigEntityExampleTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('config_entity_example');

  /**
   * The installation profile to use with this test.
   *
   * We need the 'minimal' profile in order to make sure the Tool block is
   * available.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Config Entity Example functional test',
      'description' => 'Test the Config Entity Example module.',
      'group' => 'Examples',
    );
  }

  /**
   * Various functional test of the Config Entity Example module.
   *
   * 1) Verify that the Marvin entity was created when the module was installed.
   *
   * 2) Verify that permissions are applied to the various defined paths.
   *
   * 3) Verify that we can manage entities through the user interface.
   *
   * 4) Verify that the entity we add can be re-edited.
   *
   * 5) Verify that the label is shown in the list.
   */
  public function testConfigEntityExample() {
    // 1) Verify that the Marvin entity was created when the module was
    // installed.
    $entity = entity_load('robot', 'marvin');
    $this->assertNotNull($entity, 'Marvin was created during installation.');

    // 2) Verify that permissions are applied to the various defined paths.
    // Define some paths. Since the Marvin entity is defined, we can use it
    // in our management paths.
    $forbidden_paths = array(
      '/examples/config-entity-example',
      '/examples/config-entity-example/add',
      '/examples/config-entity-example/manage/marvin',
      '/examples/config-entity-example/manage/marvin/delete',
    );
    // Check each of the paths to make sure we don't have access. At this point
    // we haven't logged in any users, so the client is anonymous.
    foreach ($forbidden_paths as $path) {
      $this->drupalGet($path);
      $this->assertResponse(403, "Access denied to anonymous for path: $path");
    }

    // Create a user with no permissions.
    $noperms_user = $this->drupalCreateUser();
    $this->drupalLogin($noperms_user);
    // Should be the same result for forbidden paths, since the user needs
    // special permissions for these paths.
    foreach ($forbidden_paths as $path) {
      $this->drupalGet($path);
      $this->assertResponse(403, "Access denied to generic user for path: $path");
    }

    // Create a user who can administer robots.
    $admin_user = $this->drupalCreateUser(array('administer robots'));
    $this->drupalLogin($admin_user);
    // Forbidden paths aren't forbidden any more.
    foreach ($forbidden_paths as $unforbidden) {
      $this->drupalGet($unforbidden);
      $this->assertResponse(200, "Access granted to admin user for path: $unforbidden");
    }

    // Now that we have the admin user logged in, check the menu links.
    $this->drupalGet('');
    $this->assertLinkByHref('examples/config-entity-example');

    // 3) Verify that we can manage entities through the user interface.
    // We still have the admin user logged in, so we'll create, update, and
    // delete an entity.
    // Go to the list page.
    $this->drupalGet('/examples/config-entity-example');
    $this->clickLink('Add robot');
    $robot_machine_name = 'roboname';
    $this->drupalPostForm(
      NULL,
      array(
        'label' => $robot_machine_name,
        'id' => $robot_machine_name,
        'floopy' => TRUE,
      ),
      t('Create Robot')
    );

    // 4) Verify that our robot appears when we edit it.
    $this->drupalGet('/examples/config-entity-example/manage/' . $robot_machine_name);
    $this->assertField('label');
    $this->assertFieldChecked('edit-floopy');

    // 5) Verify that the label and machine name are shown in the list.
    $this->drupalGet('/examples/config-entity-example');
    $this->clickLink('Add robot');
    $robby_machine_name = 'robby_machine_name';
    $robby_label = 'Robby label';
    $this->drupalPostForm(
      NULL,
      array(
        'label' => $robby_label,
        'id' => $robby_machine_name,
        'floopy' => TRUE,
      ),
      t('Create Robot')
    );
    $this->drupalGet('/examples/config-entity-example');
    $this->assertText($robby_label);
    $this->assertText($robby_machine_name);
  }

}
