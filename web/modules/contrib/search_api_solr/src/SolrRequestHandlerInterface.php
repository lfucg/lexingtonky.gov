<?php

namespace Drupal\search_api_solr;

/**
 * Provides an interface defining a SolrRequestHandler entity.
 */
interface SolrRequestHandlerInterface extends SolrConfigInterface {

  /**
   * Gets the environments targeted by this Solr RequestHandler.
   *
   * @return string[]
   *   Environments.
   */
  public function getEnvironments();

  /**
   * Gets the Solr RequestHandler definition as nested associative array.
   *
   * @return array
   *   The Solr RequestHandler definition as nested associative array.
   */
  public function getRequestHandler();

}
