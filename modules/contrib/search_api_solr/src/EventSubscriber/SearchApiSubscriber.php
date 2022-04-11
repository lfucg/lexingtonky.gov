<?php

namespace Drupal\search_api_solr\EventSubscriber;

use Drupal\search_api\Event\SearchApiEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Search API events subscriber.
 */
class SearchApiSubscriber implements EventSubscriberInterface {

  /**
   * Adds the mapping to treat some Solr special fields as fulltext in views.
   *
   * @param \Drupal\search_api\Event\MappingViewsFieldHandlersEvent $event
   *   The Search API event.
   */
  public function onMappingViewsFieldHandlers($event) {
    $mapping = & $event->getFieldHandlerMapping();

    $mapping['solr_text_omit_norms'] =
    $mapping['solr_text_suggester'] =
    $mapping['solr_text_unstemmed'] =
    $mapping['solr_text_wstoken'] = [
      'argument' => [
        'id' => 'search_api',
      ],
      'filter' => [
        'id' => 'search_api_fulltext',
      ],
      'sort' => [
        'id' => 'search_api',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Workaround to avoid a fatal error during site install in some cases.
    // @see https://www.drupal.org/project/facets/issues/3199156
    if (!class_exists('\Drupal\search_api\Event\SearchApiEvents', TRUE)) {
      return [];
    }

    return [
      SearchApiEvents::MAPPING_VIEWS_FIELD_HANDLERS => 'onMappingViewsFieldHandlers',
    ];

  }

}
