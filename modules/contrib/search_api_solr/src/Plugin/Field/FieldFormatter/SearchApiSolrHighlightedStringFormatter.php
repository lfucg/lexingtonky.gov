<?php

namespace Drupal\search_api_solr\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\search_api\Utility\Utility;

/**
 * Plugin implementation of the 'solr_highlighted_string' formatter.
 *
 * @FieldFormatter(
 *   id = "solr_highlighted_string",
 *   label = @Translation("Highlighted plain text (Search API Solr)"),
 *   field_types = {
 *     "string",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class SearchApiSolrHighlightedStringFormatter extends FormatterBase {
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
    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    /** @var \Drupal\Core\Template\TwigExtension $twigExtension */
    $twigExtension = \Drupal::service('twig.extension');

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

      $value = $twigExtension->escapeFilter($twig, $item->value);

      foreach ($highlighted_keys as $key) {
        $value = preg_replace('/(\b)('. preg_quote($key, '/') . ')(\b)/', '$1' . $this->getSetting('prefix') . '$2' . $this->getSetting('suffix') . '$3', $value);
      }

      $elements[$delta] = [
        '#markup' => nl2br($value),
      ];

      $cacheableMetadata->applyTo($elements[$delta]);
    }

    return $elements;
  }

}
