<?php

namespace Drupal\search_api_solr\Solarium\EventDispatcher;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * A helper to decorate the legacy EventDispatcherInterface::dispatch().
 */
final class Psr14Bridge extends ContainerAwareEventDispatcher implements EventDispatcherInterface {

  /**
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $dispatcher;

  public function __construct(ContainerAwareEventDispatcher $eventDispatcher) {
    $this->dispatcher = $eventDispatcher;
  }

  public function dispatch($event, Event $null = NULL) {
    if (\is_object($event)) {
      return $this->dispatcher->dispatch(\get_class($event), new EventProxy($event));
    }
    return $this->dispatcher->dispatch($event, $null);
  }

  public function addListener($event_name, $listener, $priority = 0) {
    $this->dispatcher->addListener($event_name, $listener, $priority);
  }
}
