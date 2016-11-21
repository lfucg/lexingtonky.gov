<?php

namespace Drupal\simpletest_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Demonstrate SimpleTest with a mock module.
 *
 * SimpleTestExampleMockModuleTestCase allows us to demonstrate how you can
 * use a mock module to aid in functional testing in Drupal.
 *
 * If you have some functionality that's not intrinsic to the code under test,
 * you can add a special mock module that only gets installed during test
 * time. This allows you to implement APIs created by your module, or otherwise
 * exercise the code in question.
 *
 * This test case class is very similar to SimpleTestExampleTestCase. The main
 * difference is that we enable the simpletest_example_test module by providing
 * it in the $modules property. Then we can test for behaviors provided by that
 * module.
 *
 * @see SimpleTestExampleTestCase
 *
 * @ingroup simpletest_example
 *
 * SimpleTest uses group annotations to help you organize your tests.
 *
 * @group simpletest_example
 * @group examples
 */
class SimpleTestExampleMockModuleTest extends WebTestBase {

  /**
   * Our module dependencies.
   *
   * In Drupal 8's SimpleTest, we declare module dependencies in a public
   * static property called $modules.
   *
   * @var array
   */
  static public $modules = array(
    'simpletest_example',
    'simpletest_example_test',
  );

  /**
   * Test modifications made by our mock module.
   *
   * We create a simpletest_example node and then see if our submodule
   * operated on it.
   */
  public function testSimpleTestExampleMockModule() {
    // Create a user.
    $test_user = $this->drupalCreateUser(array('access content'));
    // Log them in.
    $this->drupalLogin($test_user);
    // Set up some content.
    $settings = array(
      'type' => 'simpletest_example',
      'title' => $this->randomMachineName(32),
    );
    // Create the content node.
    $node = $this->drupalCreateNode($settings);
    // View the node.
    $this->drupalGet('node/' . $node->id());
    // Check that our module did it's thing.
    $this->assertText(t('The test module did its thing.'), "Found evidence of test module.");
  }

}
