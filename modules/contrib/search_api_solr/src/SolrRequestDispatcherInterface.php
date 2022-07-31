<?php

namespace Drupal\search_api_solr;

/**
 * Provides an interface defining a SolrRequestDispatcher entity.
 */
interface SolrRequestDispatcherInterface extends SolrConfigInterface {

  /**
   * Gets the environments targeted by this Solr RequestDispatcher.
   *
   * @return string[]
   *   Environments.
   */
  public function getEnvironments();

  /**
   * Gets the Solr RequestDispatcher definition as nested associative array.
   *
   * @return array
   *   The Solr RequestDispatcher definition as nested associative array.
   */
  public function getRequestDispatcher();

}
