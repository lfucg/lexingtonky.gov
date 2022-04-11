<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;

// Drupal >= 9.1.
if (class_exists('\Drupal\Component\EventDispatcher\Event')) {
  /**
   * Search API Solr event base class.
   */
  abstract class AbstractSearchApiQuerySolariumQueryEvent extends Event {

    /**
     * The search_api query.
     *
     * @var \Drupal\search_api\Query\QueryInterface
     */
    protected $search_api_query;

    /**
     * The solarium result.
     *
     * @var \Solarium\QueryType\Select\Query\Query
     */
    protected $solarium_query;

    /**
     * Constructs a new class instance.
     *
     * @param \Drupal\search_api\Query\QueryInterface $search_api_query
     *   The search_api query.
     * @param \Solarium\QueryType\Select\Query\Query $solarium_query
     *   The solarium query.
     */
    public function __construct(QueryInterface $search_api_query, Query $solarium_query) {
      $this->search_api_query = $search_api_query;
      $this->solarium_query = $solarium_query;
    }

    /**
     * Retrieves the search_api query.
     *
     * @return \Drupal\search_api\Query\QueryInterface
     *   The created query.
     */
    public function getSearchApiQuery() : QueryInterface {
      return $this->search_api_query;
    }

    /**
     * Retrieves the solarium query.
     *
     * @return \Solarium\QueryType\Select\Query\Query
     *   The solarium result.
     */
    public function getSolariumQuery() {
      return $this->solarium_query;
    }

  }
}
else {
  /**
   * Search API Solr event base class.
   */
  abstract class AbstractSearchApiQuerySolariumQueryEvent extends \Symfony\Component\EventDispatcher\Event {

    /**
     * The search_api query.
     *
     * @var \Drupal\search_api\Query\QueryInterface
     */
    protected $search_api_query;

    /**
     * The solarium result.
     *
     * @var \Solarium\QueryType\Select\Query\Query
     */
    protected $solarium_query;

    /**
     * Constructs a new class instance.
     *
     * @param \Drupal\search_api\Query\QueryInterface $solarium_query
     *   The search_api query.
     * @param \Solarium\QueryType\Select\Query\Query $solarium_query
     *   The solarium query.
     */
    public function __construct(QueryInterface $search_api_query, Query $solarium_query) {
      $this->search_api_query = $search_api_query;
      $this->solarium_query = $solarium_query;
    }

    /**
     * Retrieves the search_api query.
     *
     * @return \Drupal\search_api\Query\QueryInterface
     *   The created query.
     */
    public function getSearchApiQuery() : QueryInterface {
      return $this->search_api_query;
    }

    /**
     * Retrieves the solarium query.
     *
     * @return \Solarium\QueryType\Select\Query\Query
     *   The solarium result.
     */
    public function getSolariumQuery() {
      return $this->solarium_query;
    }

  }
}
