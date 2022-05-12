<?php

namespace Drupal\search_api_solr\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\search_api\Query\QueryInterface;

/**
 * Search API Solr event base class.
 */
abstract class AbstractSearchApiQuerySolariumResultEvent extends Event {

  /**
   * The search_api query.
   *
   * @var \Drupal\search_api\Query\QueryInterface
   */
  protected $search_api_query;

  /**
   * The solarium result.
   *
   * @var \Solarium\QueryType\Select\Result\Result|\Solarium\QueryType\Stream\Result
   */
  protected $solarium_result;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\search_api\Query\QueryInterface $search_api_query
   *   The search_api query.
   * @param \Solarium\QueryType\Select\Result\Result|\Solarium\QueryType\Stream\Result $solarium_result
   *   The solarium result.
   */
  public function __construct(QueryInterface $search_api_query, $solarium_result) {
    $this->search_api_query = $search_api_query;
    $this->solarium_result = $solarium_result;
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
   * Retrieves the solarium result.
   *
   * @return \Solarium\QueryType\Select\Result\Result|\Solarium\QueryType\Stream\Result
   *   The solarium result.
   */
  public function getSolariumResult() {
    return $this->solarium_result;
  }

}
