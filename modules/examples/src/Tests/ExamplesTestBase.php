<?php

namespace Drupal\examples\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * A standardized base class for Examples tests.
 *
 * Use this base class if the Examples module being tested requires menus, local
 * tasks, and actions.
 */
abstract class ExamplesTestBase extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Install Drupal.
    parent::setUp();
    // Add the system menu blocks to appropriate regions.
    $this->setupExamplesMenus();
  }

  /**
   * Set up menus and tasks in their regions.
   *
   * Since menus and tasks are now blocks, we're required to explicitly set them
   * to regions. This method standardizes the way we do that for Examples.
   *
   * Note that subclasses must explicitly declare that the block module is a
   * dependency.
   */
  protected function setupExamplesMenus() {
    $this->drupalPlaceBlock('system_menu_block:tools', ['region' => 'primary_menu']);
    $this->drupalPlaceBlock('local_tasks_block', ['region' => 'secondary_menu']);
    $this->drupalPlaceBlock('local_actions_block', ['region' => 'content']);
    $this->drupalPlaceBlock('page_title_block', ['region' => 'content']);
  }

}
