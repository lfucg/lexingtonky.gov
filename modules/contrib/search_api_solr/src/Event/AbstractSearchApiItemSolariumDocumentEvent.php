<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Query\QueryInterface;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Select\Result\Result;

// Drupal >= 9.1.
if (class_exists('\Drupal\Component\EventDispatcher\Event')) {
  /**
   * Search API Solr event base class.
   */
  abstract class AbstractSearchApiItemSolariumDocumentEvent extends Event {

    /**
     * The search_api query.
     *
     * @var \Drupal\search_api\Item\ItemInterface
     */
    protected $search_api_item;

    /**
     * The solarium document.
     *
     * @var \Solarium\Core\Query\DocumentInterface
     */
    protected $solarium_document;

    /**
     * Constructs a new class instance.
     *
     * @param \Drupal\search_api\Item\ItemInterface $search_api_item
     *   The search_api item.
     * @param \Solarium\Core\Query\DocumentInterface $solarium_document
     *   The solarium document.
     */
    public function __construct(ItemInterface $search_api_item, DocumentInterface $solarium_document) {
      $this->search_api_item = $search_api_item;
      $this->solarium_document = $solarium_document;
    }

    /**
     * Retrieves the search_api item.
     *
     * @return \Drupal\search_api\Item\ItemInterface
     *   The search_api item.
     */
    public function getSearchApiItem() : ItemInterface {
      return $this->search_api_item;
    }

    /**
     * Retrieves the solarium document.
     *
     * @return \Solarium\Core\Query\DocumentInterface
     *   The solarium document.
     */
    public function getSolariumDocument() : DocumentInterface {
      return $this->solarium_document;
    }

  }
}
else {
  /**
   * Search API Solr event base class.
   */
  abstract class AbstractSearchApiItemSolariumDocumentEvent extends \Symfony\Component\EventDispatcher\Event {

    /**
     * The search_api query.
     *
     * @var \Drupal\search_api\Item\ItemInterface
     */
    protected $search_api_item;

    /**
     * The solarium document.
     *
     * @var \Solarium\Core\Query\DocumentInterface
     */
    protected $solarium_document;

    /**
     * Constructs a new class instance.
     *
     * @param \Drupal\search_api\Item\ItemInterface $search_api_item
     *   The search_api item.
     * @param \Solarium\Core\Query\DocumentInterface $solarium_document
     *   The solarium document.
     */
    public function __construct(ItemInterface $search_api_item, DocumentInterface $solarium_document) {
      $this->search_api_item = $search_api_item;
      $this->solarium_document = $solarium_document;
    }

    /**
     * Retrieves the search_api item.
     *
     * @return \Drupal\search_api\Item\ItemInterface
     *   The search_api item.
     */
    public function getSearchApiItem() : ItemInterface {
      return $this->search_api_item;
    }

    /**
     * Retrieves the solarium document.
     *
     * @return \Solarium\Core\Query\DocumentInterface
     *   The solarium document.
     */
    public function getSolariumDocument() : DocumentInterface {
      return $this->solarium_document;
    }

  }
}
