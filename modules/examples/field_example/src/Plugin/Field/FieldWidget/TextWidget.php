<?php

namespace Drupal\field_example\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "field_example_text",
 *   module = "field_example",
 *   label = @Translation("RGB value as #ffffff"),
 *   field_types = {
 *     "field_example_rgb"
 *   }
 * )
 */
class TextWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element += array(
      '#type' => 'textfield',
      '#default_value' => $value
    );
    return array('value' => $element);
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    // $value = $element['#value'];
    // if (strlen($value) == 0) {
    //   $form_state->setValueForElement($element, '');
    //   return;
    // }
    // if (!preg_match('/^#([a-f0-9]{6})$/iD', strtolower($value))) {
    //   $form_state->setError($element, t("Color must be a 6-digit hexadecimal value, suitable for CSS."));
    // }
  }

}
