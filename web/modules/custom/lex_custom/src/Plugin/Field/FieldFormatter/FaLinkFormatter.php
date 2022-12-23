<?php

namespace Drupal\lex_custom\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Plugin implementation of the 'fa_link' formatter.
 *
 * @FieldFormatter(
 *   id = "fa_link",
 *   label = @Translation("Font Awesome Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */

class FaLinkFormatter extends LinkFormatter {

  /**
   * {@inheritdoc}
   */

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#theme'] = 'fa_link_formatter';
    }

    return $elements;
  }

}
