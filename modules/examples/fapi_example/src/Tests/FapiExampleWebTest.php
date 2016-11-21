<?php

namespace Drupal\fapi_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensure that the fapi_example forms work properly.
 *
 * @see Drupal\simpletest\WebTestBase
 *
 * SimpleTest uses group annotations to help you organize your tests.
 *
 * @group fapi_example
 *
 * @ingroup fapi_example
 */
class FapiExampleWebTest extends WebTestBase {

  /**
   * Our module dependencies.
   *
   * @var array List of test dependencies.
   */
  static public $modules = array('fapi_example');

  /**
   * The installation profile to use with this test.
   *
   * @var string Installation profile required for test.
   */
  protected $profile = 'minimal';

  /**
   * Test the ajax demo form.
   */
  public function testAjaxDemoForm() {

    // Test for a link to the ajax_demo example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/ajax-demo');

    // Verify that anonymous can access the page.
    $this->drupalGet('examples/fapi-example/ajax-demo');
    $this->assertResponse(200, 'The Demo of Ajax page is available.');

    // Post the form.
    $edit = [
      'temperature' => 'warm',
    ];
    $this->drupalPostForm('/examples/fapi-example/ajax-demo', $edit, t('Submit'));
    $this->assertText('Value for Temperature: warm');
  }

  /**
   * Test the build demo form.
   */
  public function testBuildDemo() {

    // Test for a link to the build_demo example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/build-demo');

    // Verify that anonymous can access the page.
    $this->drupalGet('examples/fapi-example/build-demo');
    $this->assertResponse(200, 'The Build Demo Form is available.');

    // Post the form.
    $edit = [
      'change' => '1',
    ];
    $this->drupalPostForm('/examples/fapi-example/build-demo', $edit, t('Submit'));
    $this->assertText('1. __construct');
    $this->assertText('2. getFormId');
    $this->assertText('3. validateForm');
    $this->assertText('4. submitForm');
  }

  /**
   * Test the container demo form.
   */
  public function testContainerDemoForm() {

    // Test for a link to the container_demo example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/container-demo');

    // Verify that anonymous can access the container_demo example page.
    $this->drupalGet('examples/fapi-example/container-demo');
    $this->assertResponse(200, 'The Demo of Container page is available.');

    // Post the form.
    $edit = [
      'name' => 'Dave',
      'pen_name' => 'DMan',
      'title' => 'My Book',
      'publisher' => 'me',
      'diet' => 'vegan',
    ];
    $this->drupalPostForm('/examples/fapi-example/container-demo', $edit, t('Submit'));
    $this->assertText('Value for name: Dave');
    $this->assertText('Value for pen_name: DMan');
    $this->assertText('Value for title: My Book');
    $this->assertText('Value for publisher: me');
    $this->assertText('Value for diet: vegan');
  }

  /**
   * Test the input demo form.
   */
  public function testInputDemoForm() {
    // Test for a link to the input_demo example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/input-demo');

    // Verify that anonymous can access the input_demo page.
    $this->drupalGet('examples/fapi-example/input-demo');
    $this->assertResponse(200, 'The Demo of Common Input Elements page is available.');

    // Post the form.
    $edit = [
      'tests_taken[SAT]' => TRUE,
      'color' => '#ff6bf1',
      'expiration' => '2015-10-21',
      'email' => 'somebody@example.org',
      'quantity' => '4',
      'password' => 'letmein',
      'password_confirm[pass1]' => 'letmein',
      'password_confirm[pass2]' => 'letmein',
      'size' => '76',
      'active' => '1',
      'search' => 'my search string',
      'favorite' => 'blue',
      'phone' => '555-555-5555',
      'table[1]' => TRUE,
      'table[3]' => TRUE,
      'text' => 'This is a test of my form.',
      'subject' => 'Form test',
      'weight' => '3',
    ];
    $this->drupalPostForm('/examples/fapi-example/input-demo', $edit, t('Submit'));
    $this->assertText('Value for What standardized tests did you take?: Array ( [SAT] =&gt; SAT )');
    $this->assertText('Value for Color: #ff6bf1');
    $this->assertText('Value for Content expiration: 2015-10-21');
    $this->assertText('Value for Email: somebody@example.org');
    $this->assertText('Value for Quantity: 4');
    $this->assertText('Value for Password: letmein');
    $this->assertText('Value for New Password: letmein');
    $this->assertText('Value for Size: 76');
    $this->assertText('Value for active: 1');
    $this->assertText('Value for Search: my search string');
    $this->assertText('Value for Favorite color: blue');
    $this->assertText('Value for Phone: 555-555-5555');
    $this->assertText('Value for Users: Array ( [1] =&gt; 1 [3] =&gt; 3 )');
    $this->assertText('Value for Text: This is a test of my form.');
    $this->assertText('Value for Subject: Form test');
    $this->assertText('Value for Weight: 3');
  }

  /**
   * Test the modal form.
   */
  public function testModalForm() {

    // Test for a link to the modal_form example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/modal-form');

    // Verify that anonymous can access the page.
    $this->drupalGet('examples/fapi-example/modal-form');
    $this->assertResponse(200, 'The Demo of Modal Form is available.');

    // Post the form.
    $edit = [
      'title' => 'My Book',
    ];
    $this->drupalPostForm('/examples/fapi-example/modal-form', $edit, t('Submit'));
    $this->assertText('Submit handler: You specified a title of My Book.');
  }

  /**
   * Check if SimpleTest Example can successfully return its main page and if
   * there is a link to the simpletest_example in the Tools menu.
   */
  public function testSimpleFormExample() {
    // Test for a link to the fapi_example in the Tools menu.
    $this->drupalGet('');
    $this->assertResponse(200, 'The Home page is available.');
    $this->assertLinkByHref('examples/fapi-example');

    // Test for a link to the simple_form example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/simple-form');

    // Verify that anonymous can access the simple_form page.
    $this->drupalGet('examples/fapi-example/simple-form');
    $this->assertResponse(200, 'The Simple Form Example page is available.');

    // Post a title.
    $edit = ['title' => 'My Custom Title'];
    $this->drupalPostForm('/examples/fapi-example/simple-form', $edit, t('Submit'));
    $this->assertText('You specified a title of My Custom Title.');
  }

  /**
   * Test the state demo form.
   */
  public function testStateDemoForm() {
    // Test for a link to the state_demo example on the fapi_example page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/state-demo');

    // Verify that anonymous can access the state_demo page.
    $this->drupalGet('examples/fapi-example/state-demo');
    $this->assertResponse(200, 'The Demo of Form State Binding page is available.');

    // Post the form.
    $edit = [
      'needs_accommodation' => TRUE,
      'diet' => 'vegan',
    ];
    $this->drupalPostForm('/examples/fapi-example/state-demo', $edit, t('Submit'));
    $this->assertText('Dietary Restriction Requested: vegan');
  }

  /**
   * Test the vertical tabs demo form.
   */
  public function testVerticalTabsDemoForm() {

    // Test for a link to the vertical_tabs_demo example on the fapi_example
    // page.
    $this->drupalGet('examples/fapi-example');
    $this->assertLinkByHref('examples/fapi-example/vertical-tabs-demo');

    // Verify that anonymous can access the vertical_tabs_demo page.
    $this->drupalGet('examples/fapi-example/vertical-tabs-demo');
    $this->assertResponse(200, 'The Demo of Container page is available.');

    // Post the form.
    $edit = [
      'name' => 'Dave',
      'publisher' => 'me',
    ];
    $this->drupalPostForm('/examples/fapi-example/container-demo', $edit, t('Submit'));
    $this->assertText('Value for name: Dave');
    $this->assertText('Value for publisher: me');
  }

}
