<?php

namespace Drupal\search_api_solr_legacy\EventSubscriber;

use Drupal\search_api_solr\Event\PostConfigSetTemplateMappingEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Search API Solr events subscriber.
 */
class SearchApiSolrSubscriber implements EventSubscriberInterface {

  /**
   * Adds the mapping Solr 3, 4 and 5.
   *
   * @param \Drupal\search_api_solr\Event\PostConfigSetTemplateMappingEvent $event
   */
  public function postConfigSetTemplateMapping(PostConfigSetTemplateMappingEvent $event) {
    $template_path = drupal_get_path('module', 'search_api_solr_legacy') . '/solr-conf-templates/';

    $solr_configset_template_mapping = $event->getConfigSetTemplateMapping();
    $solr_configset_template_mapping += [
      '3.x' => $template_path . '3.x',
      '4.x' => $template_path . '4.x',
      '5.x' => $template_path . '5.x',
    ];
    $event->setConfigSetTemplateMapping($solr_configset_template_mapping);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SearchApiSolrEvents::POST_CONFIG_SET_TEMPLATE_MAPPING][] = ['postConfigSetTemplateMapping'];

    return $events;
  }

}
