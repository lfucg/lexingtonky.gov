<?php

namespace Drupal\Tests\paragraphs_inline_entity_form\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Paragraphs IEF tests.
 *
 * @group paragraphs_inline_entity_form
 */
class ParagraphsIEFTests extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'ckeditor',
    'entity',
    'entity_browser',
    'entity_embed',
    'entity_reference',
    'paragraphs',
    'inline_entity_form',
    'paragraphs_inline_entity_form',
    'paragraphs_inline_entity_form_example'
  ];

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser(['administer site configuration']);
  }

  /**
   * Admin UI.
   */
  public function testAdminUI() {
    $this->drupalLogin($this->adminUser);
  }
}
