<?php

namespace Drupal\Tests\workbench_moderation\Traits;

/**
 * Defines a trait for common testing methods for workbench moderation.
 */
trait WorkbenchModerationTestTrait {

  /**
   * Creates a new node type.
   *
   * @param string $label
   *   The human-readable label of the type to create.
   * @param string $machine_name
   *   The machine name of the type to create.
   *
   * @return \Drupal\node\Entity\NodeType
   *   The node type just created.
   */
  protected function createNodeType($label, $machine_name) {
    /** @var \Drupal\node\Entity\NodeType $node_type */
    $node_type = $this->createContentType(['name' => $label, 'type' => $machine_name]);
    $node_type->setThirdPartySetting('workbench_moderation', 'enabled', TRUE);
    $node_type->setThirdPartySetting('workbench_moderation', 'allowed_moderation_states', [
      'draft',
      'needs_review',
      'published',
    ]);
    $node_type->save();

    return $node_type;
  }

}
