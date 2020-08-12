<?php

namespace Drupal\block_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'block_field_label' formatter.
 *
 * @FieldFormatter(
 *   id = "block_field_label",
 *   label = @Translation("Block field label"),
 *   field_types = {
 *     "block_field"
 *   }
 * )
 */
class BlockFieldLabelFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      /** @var \Drupal\block_field\BlockFieldItemInterface $item */
      $block_instance = $item->getBlock();
      // Make sure the block exists and is accessible.
      if (!$block_instance || !$block_instance->access(\Drupal::currentUser())) {
        continue;
      }

      $elements[$delta] = [
        '#markup' => $block_instance->label(),
      ];

      CacheableMetadata::createFromRenderArray($elements[$delta])
        ->merge(CacheableMetadata::createFromRenderArray($elements[$delta]['content']))
        ->addCacheableDependency($block_instance)
        ->applyTo($elements[$delta]);
    }
    return $elements;
  }

}
