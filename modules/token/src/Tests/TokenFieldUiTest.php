<?php

/**
 * @file
 * Contains \Drupal\token\Tests\TokenFieldUiTest.
 */

namespace Drupal\token\Tests;

use Drupal\node\Entity\NodeType;

/**
 * Tests field ui.
 *
 * @group token
 */
class TokenFieldUiTest extends TokenTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['path', 'token', 'token_test', 'field_ui', 'node'];

  /**
   * {@inheritdoc}
   */
  public function setUp($modules = []) {
    parent::setUp();
    $this->admin_user = $this->drupalCreateUser(['administer content types', 'administer node fields']);
    $this->drupalLogin($this->admin_user);

    $node_type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
      'description' => "Use <em>articles</em> for time-sensitive content like news, press releases or blog posts.",
    ]);
    $node_type->save();

    entity_create('field_storage_config', array(
      'field_name' => 'field_body',
      'entity_type' => 'node',
      'type' => 'text_with_summary',
    ))->save();
    entity_create('field_config', array(
      'field_name' => 'field_body',
      'label' => 'Body',
      'entity_type' => 'node',
      'bundle' => 'article',
    ))->save();
  }

  public function testBrowseByLink() {
    $this->drupalGet('admin/structure/types/manage/article/fields/node.article.field_body');
    $this->assertLink('Browse available tokens.');
    $this->assertLinkByHref('token/tree');
  }
}
