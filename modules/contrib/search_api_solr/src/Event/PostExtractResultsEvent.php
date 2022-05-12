<?php

namespace Drupal\search_api_solr\Event;

use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSetInterface;

/**
 * Event to be fired after the search result is extracted from the Solr response.
 */
final class PostExtractResultsEvent extends AbstractSearchApiQuerySolariumResultEvent {

  /**
   * The search_api result set.
   *
   * @var \Drupal\search_api\Query\ResultSetInterface
   */
  protected $result_set;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\search_api\Query\ResultSetInterface $result_set
   * @param \Drupal\search_api\Query\QueryInterface $search_api_query
   *   The search_api query.
   * @param \Solarium\QueryType\Select\Result\Result|\Solarium\QueryType\Stream\Result $solarium_result
   *   The solarium result.
   */
  public function __construct(ResultSetInterface $result_set, QueryInterface $search_api_query, $solarium_result) {
    parent::__construct($search_api_query, $solarium_result);
    $this->result_set = $result_set;
  }

  /**
   * Retrieves the search_api result set.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   */
  public function getSearchApiResultSet() : ResultSetInterface {
    return $this->result_set;
  }

}
