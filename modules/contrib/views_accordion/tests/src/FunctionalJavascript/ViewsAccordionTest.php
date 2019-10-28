<?php

namespace Drupal\Tests\views_accordion\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the JavaScript functionality of the Views Accordion module.
 *
 * @group views_accordion
 */
class ViewsAccordionTest extends WebDriverTestBase {
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views_accordion_test',
  ];

  /**
   * The nodes created as part of this test.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes = [];

  /**
   * The first user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user1;

  /**
   * The second user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user2;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create a set of users and nodes for testing.
    $this->user1 = $this->createUser();
    $this->user2 = $this->createUser();
    // Test view views_accordion_test_grouping is set to group by uid.
    $this->nodes[] = $this->createNode(['uid' => $this->user1->id()]);
    $this->nodes[] = $this->createNode(['uid' => $this->user1->id()]);
    $this->nodes[] = $this->createNode(['uid' => $this->user2->id()]);
    $this->nodes[] = $this->createNode(['uid' => $this->user2->id()]);
  }

  /**
   * Tests Views Accordion functionality.
   */
  public function testViewsAccordion() {
    $this->drupalGet('views-accordion-test');
    $driver = $this->getSession()->getDriver();

    // Assert our JS settings are available.
    $settings = $this->getDrupalSettings();
    $this->assertArrayHasKey('views_accordion', $settings, 'Views accordion JS settings avaialable');

    // Assert that the first row is visible but not the second.
    $driver->isVisible($this->cssSelectToXpath('#ui-id-2'));
    $this->assertFalse($driver->isVisible($this->cssSelectToXpath('#ui-id-4')), 'Row two is collapsed');

    // Assert that clicking the first row does not close it.
    $this->click('#ui-id-1');
    $driver->isVisible($this->cssSelectToXpath('#ui-id-2'));

    // Assert the header icons are displayed in the correct place.
    $driver->isVisible($this->cssSelectToXpath('#ui-id-1 span.ui-icon-triangle-1-s'));
    $driver->isVisible($this->cssSelectToXpath('#ui-id-3 span.ui-icon-triangle-1-e'));

    // Test the grouping functionality.
    $this->drupalGet('views-accordion-test-grouping');
    // Assert the first header is the first user name.
    $this->assertEquals($this->user1->getAccountName(), $driver->getText($this->cssSelectToXpath('#ui-id-1')));
    $first_group_xpath = $this->cssSelectToXpath('#ui-id-2');
    $driver->isVisible($first_group_xpath);
    // Assert correct nodes are in the first group.
    $first_group_content = $driver->getText($first_group_xpath);
    $this->assertContains($this->nodes[0]->getTitle(), $first_group_content, 'First node is on first accordion group');
    $this->assertContains($this->nodes[1]->getTitle(), $first_group_content, 'Second node is on first accordion group');
    $this->assertNotContains($this->nodes[2]->getTitle(), $first_group_content, 'Third node is not on first accordion group');
    $this->assertNotContains($this->nodes[3]->getTitle(), $first_group_content, 'Fourth node is not on first accordion group');

    // Assert the second accordion header is the second user name.
    $this->assertEquals($this->user2->getAccountName(), $driver->getText($this->cssSelectToXpath('#ui-id-3')));
    $second_group_xpath = $this->cssSelectToXpath('#ui-id-4');
    $this->assertFalse($driver->isVisible($second_group_xpath), 'Second accordion group is collapsed');
    $this->click('#ui-id-3');
    $driver->isVisible($second_group_xpath);
    // Assert correct nodes are in the second group.
    $second_group_content = $driver->getText($second_group_xpath);
    $this->assertNotContains($this->nodes[0]->getTitle(), $second_group_content, 'First node is not on second accordion group');
    $this->assertNotContains($this->nodes[1]->getTitle(), $second_group_content, 'Second node is not on second accordion group');
    $this->assertContains($this->nodes[2]->getTitle(), $second_group_content, 'Third node is on second accordion group');
    $this->assertContains($this->nodes[3]->getTitle(), $second_group_content, 'Fourth node is on second accordion group');
  }

}
