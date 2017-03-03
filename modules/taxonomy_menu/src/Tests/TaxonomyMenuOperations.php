<?php

/**
 * @file
 * Definition of Drupal\taxonomy_menu\Tests\KeyService.
 */

namespace Drupal\taxonomy_menu\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the operations of Taxonomy Menu.
 *
 * @group taxonomy_menu
 */
class TaxonomyMenuOperations extends WebTestBase {

  public static $modules = array('taxonomy_menu', 'system', 'menu_ui', 'taxonomy', 'dblog');

  /**
   * Set up for all tests.
   */
  function setUp() {
    parent::setUp();

    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser(array('administer site configuration', 'administer taxonomy'));
    $this->drupalLogin($user1);

    // Create a testing taxonomy vocabulary.
    $this->drupalGet('admin/structure/taxonomy/add');

    $edit = [
      'vid' => 'test_tax_vocab',
      'name' => 'Test',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Create logged in user.
    $perms = [
      'administer site configuration',
      'administer taxonomy',
      'administer menu'
      //'delete terms in test',
      //'edit terms in test'
    ];
    $admin_user = $this->drupalCreateUser($perms);
    $this->drupalLogin($admin_user);

    // Add sample terms to the vocabulary.
    $this->drupalGet('admin/structure/taxonomy/manage/test_tax_vocab/add');
    $edit = [
      'name[0][value]' => 'test term 1',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->drupalGet('admin/structure/taxonomy/manage/test_tax_vocab/add');
    $edit = [
      'name[0][value]' => 'test term 1-A',
      'parent[]' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->drupalGet('admin/structure/taxonomy/manage/test_tax_vocab/add');
    $edit = [
      'name[0][value]' => 'test term 2',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Create a testing menu.
    $this->drupalGet('admin/structure/menu/add');
    $edit = [
      'id' => 'test-menu',
      'label' => 'Test',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Create new taxonomy menu.
    $this->drupalGet('admin/structure/taxonomy_menu/add');
    $edit = [
      'id' => 'test_tax_menu',
      'label' => 'test tax menu',
      'vocabulary' => 'test_tax_vocab',
      'menu' => 'test-menu',
      'expanded' => 1,
      'depth' => '1',
      'menu_parent' => 'test-menu:',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
  }

  /**
   * Test creation of taxonomy menu functions.
   */
  function testTaxMenuCreate() {


    // Check menu for taxonomy-based menu items keyed 1, 2, and 3.
    $this->drupalGet('admin/structure/menu/manage/test-menu');
    $this->assertFieldByName(
      'links[menu_plugin_id:taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.1][enabled]',
      NULL,
      'I should expect to see enabled field for taxonomy term 1'
    );
    $this->assertFieldByName(
      'links[menu_plugin_id:taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.2][enabled]',
      NULL,
      'I should expect to see enabled field for taxonomy term 2'
    );
    $this->assertFieldByName(
      'links[menu_plugin_id:taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.3][enabled]',
      NULL,
      'I should expect to see enabled field for taxonomy term 3'
    );

    // Check 2 is a parent of 1.
    $this->assertFieldByName(
      'links[menu_plugin_id:taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.2][parent]',
      'taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.1',
      'I should expect to see taxonomy term 2 have a parent of taxonomy term 1'
    );

  }

  /**
   * Test creation of taxonomy term.
   */
  function testTaxTermCreate() {

    // Create a new term.
    $this->drupalGet('admin/structure/taxonomy/manage/test_tax_vocab/add');
    $edit = [
      'name[0][value]' => 'test term 3',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->drupalGet('admin/structure/menu/manage/test-menu');
    // Check for it within the menu.
    $this->assertFieldByName(
      'links[menu_plugin_id:taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.4][enabled]',
      NULL,
      'I should expect to see enabled field for taxonomy term 4'
    );
  }

  /**
   * Test deletion of taxonomy term.
   */
  function testTaxTermDelete() {

    // Delete a term.
    $this->drupalGet('taxonomy/term/3/delete');
    $edit = [
    ];
    $this->drupalPostForm(NULL, $edit, t('Delete'));

    // Check for it within the menu.
    $this->assertNoFieldByName(
      'links[menu_plugin_id:taxonomy_menu.menu_link.test.3][enabled]',
      NULL,
      'I should not expect to see enabled field for taxonomy term 3'
    );
  }

  /**
   * Tests if of menu links from taxonony_menu is expanded.
   */
  function testTaxMenuLinkExpanded() {
    $this->drupalGet('admin/structure/menu/link/taxonomy_menu.menu_link:taxonomy_menu.menu_link.test_tax_menu.1/edit');

    $this->assertFieldByName(
      'expanded',
      1,
      'I should expect to see expanded value for menu based on taxonomy term 1'
    );
  }

}
