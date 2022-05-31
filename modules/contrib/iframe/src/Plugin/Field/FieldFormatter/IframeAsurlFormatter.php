<?php

namespace Drupal\iframe\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class IframeAsurlFormatter.
 *
 * @FieldFormatter(
 *  id = "iframe_asurl",
 *  label = @Translation("A link with the given title"),
 *  field_types = {"iframe"}
 * )
 */
class IframeAsurlFormatter extends IframeDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if (empty($item->url)) {
        continue;
      }
      if (!(property_exists($item, 'title') && $item->title !== null)) {
        $item->title = '';
      }
      $linktext = empty($item->title) ? $item->url : $item->title;
      $elements[$delta] = [
        '#markup' => Link::fromTextAndUrl($linktext, Url::fromUri($item->url, ['title' => $item->title]))->toString(),
        '#allowed_tags' => ['iframe', 'a', 'h1', 'h2', 'h3', 'h4'],
      ];
    }
    return $elements;
  }

}
