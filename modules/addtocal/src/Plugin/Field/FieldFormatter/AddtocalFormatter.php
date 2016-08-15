<?php

/**
 * @file
 * Contains \Drupal\addtocal\Plugin\Field\FieldFormatter\AddtocalFormatter.
 */

namespace Drupal\addtocal\Plugin\Field\FieldFormatter;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'Add to Cal' formatter.
 *
 * @FieldFormatter(
 *   id = "addtocal",
 *   label = @Translation("Add to Cal"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class AddtocalFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = array(
        '#type' => 'link',
        '#title' => 'Add to Outlook',
        '#url' =>  Url::fromRoute('addtocal.controller', ['nid' => $items->getEntity()->id()])
      );
    }

    return $element;
  }
}
