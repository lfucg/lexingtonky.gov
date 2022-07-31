<?php

namespace Drupal\Tests\diff\Functional;

/**
 * Tests field visibility when using a custom view mode.
 *
 * @group diff
 */
class DiffViewModeTest extends DiffTestBase {

  use CoreVersionUiTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['field_ui'];

  /**
   * Tests field visibility using a cutom view mode.
   */
  public function testViewMode() {
    $this->drupalLogin($this->rootUser);

    // Create a node.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Sample node',
      'body' => [
        'value' => 'Foo',
      ],
    ]);

    // Edit the article and change the email.
    $edit = array(
      'body[0][value]' => 'Fighters',
      'revision' => TRUE,
    );
    $this->drupalPostNodeForm('node/' . $node->id() . '/edit', $edit, 'Save and keep published');

    // Set the Body field to hidden in the diff view mode.
    $edit = [
      'fields[body][region]' => 'hidden',
    ];
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->submitForm($edit, 'Save');
    $edit = [
      'fields[body][region]' => 'hidden',
    ];
    $this->drupalGet('admin/structure/types/manage/article/display/teaser');
    $this->submitForm($edit, 'Save');

    // Check the difference between the last two revisions.
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->submitForm([], 'Compare selected revisions');
    $this->assertSession()->pageTextNotContains('Body');
    $this->assertSession()->pageTextNotContains('Foo');
    $this->assertSession()->pageTextNotContains('Fighters');
  }

}
