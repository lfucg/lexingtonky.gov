<?php

namespace Drupal\search_api_solr\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\search_api\Utility\Utility;

/**
 * Plugin implementation of the 'solr_highlighted_text_default' formatter.
 *
 * @FieldFormatter(
 *   id = "solr_highlighted_text_default",
 *   label = @Translation("Highlighted text (Search API Solr)"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   },
 *   quickedit = {
 *     "editor" = "form"
 *   }
 * )
 */
class SearchApiSolrHighlightedTextDefaultFormatter extends FormatterBase {
  use SearchApiSolrHighlightedFormatterSettingsTrait;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   *
   * @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter::viewValue()
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\search_api\Utility\QueryHelperInterface $queryHelper */
    $queryHelper = \Drupal::service('search_api.query_helper');

    $elements = [];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $item->getEntity();
      $item_id = Utility::createCombinedId('entity:' . $entity->getEntityTypeId(),$entity->id() . ':' . $item->getLangcode());
      $highlighted_keys = [];
      $cacheableMetadata = New CacheableMetadata();
      $cacheableMetadata->addCacheableDependency($entity);

      foreach ($queryHelper->getAllResults() as $resultSet) {
        foreach ($resultSet->getResultItems() as $resultItem) {
          if ($resultItem->getId() === $item_id) {
            $cacheableMetadata->addCacheableDependency($resultSet->getQuery());
            if ($highlighted_keys_tmp = $resultItem->getExtraData('highlighted_keys')) {
              $highlighted_keys = $highlighted_keys_tmp;
              break 2;
            }
          }
        }
      }

      $value = $item->value;
      foreach ($highlighted_keys as $key) {
        $value = preg_replace('/(\b)('. preg_quote($key, '/') . ')(\b)/', '$1' . $this->getSetting('prefix') . '$2' . $this->getSetting('suffix') . '$3', $value);
      }

      $elements[$delta] = [
        '#type' => 'processed_text',
        '#text' => $value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];

      $cacheableMetadata->applyTo($elements[$delta]);
    }

    return $elements;
  }

}
