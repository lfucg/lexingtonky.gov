<?php

namespace Drupal\search_api_solr\Solarium\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * A proxy for events defined by symfony contracts to be used with older Drupal.
 */
class EventProxy extends Event
{
  /**
   * @var \Symfony\Contracts\EventDispatcher\Event
   */
  protected $event;

  public function __construct($event) {
    $this->event = $event;
  }

  public function isPropagationStopped()
  {
    return $this->event->isPropagationStopped();
  }

  public function stopPropagation()
  {
    $this->event->stopPropagation();
  }

  /**
   * Proxies all method calls to the original event.
   */
  public function __call($method, $arguments)
  {
    return $this->event->{$method}(...$arguments);
  }
}
