<?php

namespace Drupal\field_example\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_example_colorpicker' widget.
 *
 * @FieldWidget(
 *   id = "field_example_colorpicker",
 *   module = "field_example",
 *   label = @Translation("Color Picker"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ColorPickerWidget extends TextWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value'] += array(
      '#suffix' => '<div class="field-example-colorpicker"></div><div id="map"></div><link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.1/dist/leaflet.css" />',
      '#attributes' => array('class' => array('edit-field-example-colorpicker')),
      '#attached' => array(
        'library' => array(
          'field_example/colorpicker',
          'field_example/leaflet',
        ),
      ),
    );

    return $element;
  }

}
