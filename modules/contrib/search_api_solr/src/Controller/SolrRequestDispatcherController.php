<?php

namespace Drupal\search_api_solr\Controller;

use Drupal\search_api\ServerInterface;
use Drupal\search_api_solr\SolrConfigInterface;

/**
 * Provides different listings of SolrRequestDispatcher.
 */
class SolrRequestDispatcherController extends AbstractSolrEntityController {

  /**
   * Entity type id.
   *
   * @var string
   */
  protected $entityTypeId = 'solr_request_dispatcher';

  /**
   * Disables a Solr Entity on this server.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   Search API server.
   * @param \Drupal\search_api_solr\SolrConfigInterface $solr_request_dispatcher
   *   Solr request dispatcher.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function disableOnServer(ServerInterface $search_api_server, SolrConfigInterface $solr_request_dispatcher) {
    return parent::disableOnServer($search_api_server, $solr_request_dispatcher);
  }

  /**
   * Enables a Solr Entity on this server.
   *
   * @param \Drupal\search_api\ServerInterface $search_api_server
   *   Serach API server.
   * @param \Drupal\search_api_solr\SolrConfigInterface $solr_request_dispatcher
   *   Solr request dispatcher.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function enableOnServer(ServerInterface $search_api_server, SolrConfigInterface $solr_request_dispatcher) {
    return parent::enableOnServer($search_api_server, $solr_request_dispatcher);
  }

}
