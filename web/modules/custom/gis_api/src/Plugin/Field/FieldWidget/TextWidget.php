<?php

namespace Drupal\gis_api\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "field_example_text",
 *   module = "gis_api",
 *   label = @Translation("RGB value as #ffffff"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TextWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = $items[$delta]->value ?? '';
    $element += [
      '#type' => 'textfield',
      '#default_value' => $value,
    ];
    return ['value' => $element];
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {

  }

}
