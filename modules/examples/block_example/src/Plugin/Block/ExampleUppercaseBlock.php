<?php

namespace Drupal\block_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Example: uppercase this please' block.
 *
 * @Block(
 *   id = "example_uppercase",
 *   admin_label = @Translation("Example: uppercase this please")
 * )
 */
class ExampleUppercaseBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->t("This block's title will be changed to uppercase. Any other block with 'uppercase' in the subject or title will also be altered. If you change this block's title through the UI to omit the word 'uppercase', it will still be altered to uppercase as the subject key has not been changed."),
    );
  }

}
