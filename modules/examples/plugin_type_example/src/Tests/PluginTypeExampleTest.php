<?php

namespace Drupal\plugin_type_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the functionality of the Plugin Type Example module.
 *
 * @ingroup plugin_type_example
 *
 * @group plugin_type_example
 * @group examples
 */
class PluginTypeExampleTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('plugin_type_example');

  /**
   * The installation profile to use with this test.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Test the plugin manager can be loaded, and the plugins are registered.
   */
  public function testPluginExample() {
    $manager = \Drupal::service('plugin.manager.sandwich');

    $sandwich_plugin_definitions = $manager->getDefinitions();

    // Check we have only one sandwich plugin.
    $this->assertEqual(count($sandwich_plugin_definitions), 2, 'There are two sandwich plugins defined.');

    // Check some of the properties of the ham sandwich plugin definition.
    $sandwich_plugin_definition = $sandwich_plugin_definitions['ham_sandwich'];
    $this->assertEqual($sandwich_plugin_definition['calories'], 500, 'The ham sandwich plugin definition\'s calories property is set.');

    // Check the alter hook fired and changed a property.
    $this->assertEqual($sandwich_plugin_definition['foobar'], 'We have altered this in the alter hook', 'The ham sandwich plugin definition\'s foobar property is set, and was correctly altered by the plugin info alter hook.');

    // Create an instance of the ham sandwich plugin to check it works.
    $plugin = $manager->createInstance('ham_sandwich', array('of' => 'configuration values'));

    $this->assertEqual(get_class($plugin), 'Drupal\plugin_type_example\Plugin\Sandwich\ExampleHamSandwich', 'The ham sandwich plugin is instantiated and of the correct class.');
  }

  /**
   * Test the output of the example page.
   */
  public function testPluginExamplePage() {
    $this->drupalGet('examples/plugin-type-example');
    $this->assertResponse(200, 'Example page successfully accessed.');

    // Check we see the plugin id.
    $this->assertText(t('ham_sandwich'), 'The plugin ID is output.');

    // Check we see the plugin description.
    $this->assertText(t('Ham, mustard, rocket, sun-dried tomatoes.'), 'The plugin description is output.');
  }

}
