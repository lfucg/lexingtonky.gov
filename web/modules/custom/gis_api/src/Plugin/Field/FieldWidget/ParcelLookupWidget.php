<?php

namespace Drupal\gis_api\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'parcel_lookup' widget.
 *
 * @FieldWidget(
 *   id = "parcel_lookup",
 *   module = "gis_api",
 *   label = @Translation("Lex Parcel Lookup"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ParcelLookupWidget extends TextWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value'] += [
      '#suffix' => '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.1/dist/leaflet.css" />',
      '#attributes' => ['class' => ['edit-field-parcel-lookup']],
      '#attached' => [
        'library' => [
          'gis_api/lex_gis_api',
          'gis_api/leaflet',
        ],
      ],
    ];

    return $element;
  }

}
