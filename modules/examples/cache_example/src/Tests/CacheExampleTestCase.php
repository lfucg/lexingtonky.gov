<?php

namespace Drupal\cache_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for the cache_example module.
 *
 * @ingroup cache_example
 *
 * @group cache_example
 * @group examples
 */
class CacheExampleTestCase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('cache_example');

  /**
   * The installation profile to use with this test.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Test menu links and routes.
   *
   * Test the following:
   * - A link to the cache_example in the Tools menu.
   * - That you can successfully access the cache_example form.
   */
  public function testCacheExampleMenu() {

    // Test for a link to the cache_example in the Tools menu.
    $this->drupalGet('');
    $this->assertResponse(200, 'The Home page is available.');
    $this->assertLinkByHref('examples/cache-example');

    // Verify if the can successfully access the cache_example form.
    $this->drupalGet('examples/cache-example');
    $this->assertResponse(200, 'The Cache Example description page is available.');
  }

  /**
   * Test that our caches function.
   *
   * Does the following:
   * - Load cache example page and test if displaying uncached version.
   * - Reload once again and test if displaying cached version.
   * - Find reload link and click on it.
   * - Clear cache at the end and test if displaying uncached version again.
   */
  public function testCacheExampleBasic() {

    // We need administrative privileges to clear the cache.
    $admin_user = $this->drupalCreateUser(array('administer site configuration'));
    $this->drupalLogin($admin_user);

    // Get initial page cache example page, first time accessed,
    // and assert uncached output.
    $this->drupalGet('examples/cache-example');
    $this->assertText('Source: actual file search');

    // Reload the page; the number should be cached.
    $this->drupalGet('examples/cache-example');
    $this->assertText('Source: cached');

    // Now push the button to remove the count.
    $this->drupalPostForm('examples/cache-example', array(), t('Explicitly remove cached file count'));
    $this->assertText('Source: actual file search');

    // Create a cached item. First make sure it doesn't already exist.
    $this->assertText('Cache item does not exist');
    $this->drupalPostForm('examples/cache-example', array('expiration' => -10), t('Create a cache item with this expiration'));
    // We should now have an already-expired item. Automatically invalid.
    $this->assertText('Cache_item is invalid');
    // Now do the expiration operation.
    $this->drupalPostForm('examples/cache-example', array('cache_clear_type' => 'expire'), t('Clear or expire cache'));
    // And verify that it was removed.
    $this->assertText('Cache item does not exist');

    // Create a cached item. This time we'll make it not expire.
    $this->drupalPostForm('examples/cache-example', array('expiration' => 'never_remove'), t('Create a cache item with this expiration'));
    // We should now have an never-remove item.
    $this->assertText('Cache item exists and is set to expire at Never expires');
    // Now do the expiration operation.
    $this->drupalPostForm('examples/cache-example', array('cache_clear_type' => 'expire'), t('Clear or expire cache'));
    // And verify that it was not removed.
    $this->assertText('Cache item exists and is set to expire at Never expires');
    // Now do tag invalidation.
    $this->drupalPostForm('examples/cache-example', array('cache_clear_type' => 'remove_tag'), t('Clear or expire cache'));
    // And verify that it was invalidated.
    $this->assertText('Cache_item is invalid');
    // Do the hard delete.
    $this->drupalPostForm('examples/cache-example', array('cache_clear_type' => 'remove_all'), t('Clear or expire cache'));
    // And verify that it was removed.
    $this->assertText('Cache item does not exist');
  }

}
