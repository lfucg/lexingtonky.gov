<?php

/**
 * @file
 * Contains \Drupal\workbench_moderation\Tests\ModerationStateBlockTest.
 */

namespace Drupal\workbench_moderation\Tests;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Component\Utility\Unicode;

/**
 * Tests general content moderation workflow for blocks.
 *
 * @group workbench_moderation
 */
class ModerationStateBlockTest extends ModerationStateTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create the "basic" block type.
    $bundle = BlockContentType::create([
      'id' => 'basic',
      'label' => 'basic',
      'revision' => FALSE,
    ]);
    $bundle->save();

    // Add the body field to it.
    block_content_add_body_field($bundle->id());
  }

  /**
   * Tests moderating custom blocks.
   */
  public function testCustomBlockModeration() {
    $this->drupalLogin($this->rootUser);

    // Enable moderation for custom blocks at admin/structure/block/block-content/manage/basic/moderation.
    $edit = [
      'enable_moderation_state' => TRUE,
      'allowed_moderation_states[draft]' => TRUE,
      'allowed_moderation_states[published]' => TRUE,
      'default_moderation_state' => 'draft',
    ];
    $this->drupalPostForm('admin/structure/block/block-content/manage/basic/moderation', $edit, t('Save'));
    $this->assertText(t('Your settings have been saved.'));

    // Create a custom block at block/add and save it as draft.
    $body = 'Body of moderated block';
    $edit = [
      'info[0][value]' => 'Moderated block',
      'body[0][value]' => $body,
    ];
    $this->drupalPostForm('block/add', $edit, t('Save and Create New Draft'));
    $this->assertText(t('basic Moderated block has been created.'));

    // Place the block in the Sidebar First region.
    $instance = array(
      'id' => 'moderated_block',
      'settings[label]' => $edit['info[0][value]'],
      'region' => 'sidebar_first',
    );
    $block = BlockContent::load(1);
    $url = 'admin/structure/block/add/block_content:' . $block->uuid() . '/' . $this->config('system.theme')->get('default');
    $this->drupalPostForm($url, $instance, t('Save block'));

    // Navigate to home page and check that the block is visible.
    $this->drupalGet('');
    $this->assertText($body);

    // Open the form to edit the block:
    $updated_body = 'This is the new body value';
    $edit = [
      'body[0][value]' => $updated_body,
    ];
    $this->drupalPostForm('block/' . $block->id(), $edit, t('Save and Create New Draft'));
    $this->assertText(t('basic Moderated block has been updated.'));

    // Check that we still see the current revision of the block as the new one
    // has not been approved yet.
    $this->drupalGet('');
    $this->assertNoText($updated_body);

    // Open the latest tab and publish the new draft.
    $edit = [
      'new_state' => 'published',
    ];
    $this->drupalPostForm('block/' . $block->id() . '/latest', $edit, t('Apply'));
    $this->assertText(t('The moderation state has been updated.'));

    // Navigate to home page and check that the latest revision is now visible.
    $this->drupalGet('');
    $this->assertText($updated_body);
  }

}
