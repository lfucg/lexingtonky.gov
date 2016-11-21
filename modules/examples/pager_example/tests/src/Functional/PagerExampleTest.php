<?php

namespace Drupal\Tests\pager_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests paging.
 *
 * @group pager_example
 */
class PagerExampleTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['pager_example', 'node'];

  /**
   * Confirms nodes paging works correctly on page "pager_example".
   */
  public function testPagerExamplePage() {
    $nodes = array();
    $nodes[] = $this->drupalCreateNode();

    $this->drupalGet('examples/pager-example');
    $this->assertNoLink('Next');
    $this->assertNoLink('Previous');

    // Create new 5 nodes.
    for ($i = 1; $i <= 5; $i++) {
      $nodes[] = $this->drupalCreateNode();
    }

    // drupalGet() caches results for anonymous users so we should
    // flush caches.
    drupal_flush_all_caches();

    // Check 'Next' link on first page.
    $this->drupalGet('examples/pager-example');
    $this->assertLinkByHref('?page=1');
    $this->assertRaw($nodes[5]->getTitle(), 'Node 6 appears on page 1.');

    // Check the last page.
    $this->drupalGet('examples/pager-example', array('query' => array('page' => 2)));
    $this->assertNoLink('Next');
    $this->assertLinkByHref('?page=1');;
    $this->assertRaw($nodes[1]->getTitle(), 'Node 1 appears on page 3.');
  }

}
