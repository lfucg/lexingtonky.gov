<?php

namespace Drupal\field_example\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_example_color_background' formatter.
 *
 * @FieldFormatter(
 *   id = "field_example_color_background",
 *   label = @Translation("Change the background of the output text"),
 *   field_types = {
 *     "field_example_rgb"
 *   }
 * )
 */
class ColorBackgroudFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    foreach ($items as $delta => $item) {
      $elements[$delta] = array(
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => t('The content area color has been changed to @code', array('@code' => $item->value)),
        '#attributes' => array(
          'style' => 'background-color: ' . $item->value,
        ),
      );
    }
    return $elements;
  }

}
