<?php

namespace Drupal\iframe\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'Iframe' widget with URL.
 *
 * @FieldWidget(
 *   id = "iframe_url",
 *   label = @Translation("URL only"),
 *   field_types = {"iframe"}
 * )
 */
class IframeUrlWidget extends IframeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $on_admin_page = isset($element['#field_parents'][0]) && ('default_value_input' == $element['#field_parents'][0]);
    if (!$on_admin_page) {
      $this->allowedAttributes['width'] = 0;
      $this->allowedAttributes['height'] = 0;
    }
    $elements = parent::formElement($items, $delta, $element, $form, $form_state);
    if (!$on_admin_page) {
      // Dont show, only save default value.
      $elements['width']['#type'] = 'value';
      unset($element['width']['#required']);
      // Dont show, only save default value.
      $elements['height']['#type'] = 'value';
      unset($element['height']['#required']);
    }

    return $elements;
  }

}
