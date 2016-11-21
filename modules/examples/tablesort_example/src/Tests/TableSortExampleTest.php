<?php

namespace Drupal\tablesort_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Verify the tablesort functionality.
 *
 * @group tablesort_example
 * @group examples
 *
 * @ingroup tablesort_example
 */
class TableSortExampleTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('tablesort_example');

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
   * Verify the functionality of the sortable table.
   */
  public function testTableSortExampleBasic() {
    // No need to login for this test.
    $this->drupalGet('/examples/tablesort-example', array('query' => array('sort' => 'desc', 'order' => 'Numbers')));
    $this->assertFieldByXPath('//tbody/tr/td[1]', 7, 'Ordered by Number decending.');

    $this->drupalGet('/examples/tablesort-example', array('query' => array('sort' => 'asc', 'order' => 'Numbers')));
    $this->assertFieldByXPath('//tbody/tr/td[1]', 1, 'Ordered by Number ascending.');

    // Sort by Letters.
    $this->drupalGet('/examples/tablesort-example', array('query' => array('sort' => 'desc', 'order' => 'Letters')));
    $this->assertFieldByXPath('//tbody/tr/td[2]', 'w', 'Ordered by Letters decending.');

    $this->drupalGet('/examples/tablesort-example', array('query' => array('sort' => 'asc', 'order' => 'Letters')));
    $this->assertFieldByXPath('//tbody/tr/td[2]', 'a', 'Ordered by Letters ascending.');

    // Sort by Mixture.
    $this->drupalGet('/examples/tablesort-example', array('query' => array('sort' => 'desc', 'order' => 'Mixture')));
    $this->assertFieldByXPath('//tbody/tr/td[3]', 't982hkv', 'Ordered by Mixture decending.');

    $this->drupalGet('/examples/tablesort-example', array('query' => array('sort' => 'asc', 'order' => 'Mixture')));
    $this->assertFieldByXPath('//tbody/tr/td[3]', '0kuykuh', 'Ordered by Mixture ascending.');
  }

  /**
   * Data provider for testing menu links.
   *
   * @return array
   *   Array of page -> link relationships to check for.
   *   The key is the path to the page where our link should appear.
   *   The value is the link that should appear on that page.
   */
  protected function providerMenuLinks() {
    return array(
      '' => '/examples/tablesort-example',
    );
  }

  /**
   * Verify and validate that default menu links were loaded for this module.
   */
  public function testTableSortExampleLink() {
    $links = $this->providerMenuLinks();
    foreach ($links as $page => $path) {
      $this->drupalGet($page);
      $this->assertLinkByHref($path);
    }
  }

  /**
   * Tests tablesort_example menus.
   */
  public function testTableSortExampleMenu() {
    $this->drupalGet('/examples/tablesort-example');
    $this->assertResponse(200, 'Description page exists.');
  }

}
