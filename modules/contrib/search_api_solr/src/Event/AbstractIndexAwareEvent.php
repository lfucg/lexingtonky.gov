<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\search_api\IndexInterface;

abstract class AbstractIndexAwareEvent extends Event {

  /**
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\search_api\IndexInterface $index
   */
  public function __construct(IndexInterface $index) {
    $this->index = $index;
  }

  /**
   * Retrieves the index.
   */
  public function getIndex(): IndexInterface {
    return $this->index;
  }
}
