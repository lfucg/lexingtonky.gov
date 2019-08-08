<?php

namespace Drupal\Tests\admin_toolbar\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Test the functionality of admin toolbar search.
 *
 * @group admin_toolbar
 */
class AdminToolbarSearchTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'admin_toolbar',
  ];

  /**
   * The admin user for tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access toolbar',
      'administer menu',
      'access administration pages',
      'administer site configuration',
    ]);
  }

  /**
   * Tests search functionality.
   */
  public function testSearchFunctionality() {

    $search_tab = '#toolbar-item-administration-search';
    $search_tray = '#toolbar-item-administration-search-tray';
    $search_input = '#admin-toolbar-search-input';

    $this->drupalLogin($this->adminUser);
    $this->assertSession()->responseContains('admin.toolbar_search.css');
    $this->assertSession()->responseContains('admin_toolbar_search.js');
    $this->assertSession()->elementExists('css', $search_tab)->click();
    $this->assertSession()->waitForElementVisible('css', $search_tray);

    $this->assertSession()
      ->elementExists('css', $search_input)
      ->setValue('basic');
    $autocomplete_suggestions = $this->assertSession()
      ->waitForElementVisible('css', 'ul.ui-autocomplete');

    $suggestion = 'Configuration &gt; System &gt; Basic site settings <span class="admin-toolbar-search-url">/subdirectory/admin/config/system/site-information</span>';
    $this->assertSession()
      ->elementContains('css', 'ul.ui-autocomplete', $suggestion);

  }

}
