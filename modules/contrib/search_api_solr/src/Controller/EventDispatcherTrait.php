<?php

namespace Drupal\search_api_solr\Controller;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Provides different listings of SolrFieldType.
 */
trait EventDispatcherTrait {

  /**
   * @var ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Returns the event dispatcher.
   *
   * @return ContainerAwareEventDispatcher
   */
  protected function eventDispatcher(): ContainerAwareEventDispatcher {
    if (!$this->eventDispatcher) {
      $this->eventDispatcher = \Drupal::getContainer()->get('event_dispatcher');
    }
    return $this->eventDispatcher;
  }

}
