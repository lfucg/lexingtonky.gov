<?php

namespace Drupal\search_api_pantheon\Traits;

use Drupal\search_api_pantheon\Services\Endpoint;

/**
 * Endpoint Aware Trait.
 */
trait EndpointAwareTrait {

  /**
   * The endpoint in question.
   *
   * @var \Drupal\search_api_pantheon\Services\Endpoint
   */
  protected Endpoint $endpoint;

  /**
   * Getter for var.
   *
   * @return \Solarium\Core\Client\Endpoint
   *   Endpoint in question.
   */
  public function getEndpoint(): Endpoint {
    return $this->endpoint;
  }

  /**
   * Setter for Var.
   *
   * @param \Solarium\Core\Client\Endpoint $endpoint
   *   Endpoint in question.
   */
  public function setEndpoint(Endpoint $endpoint) {
    $this->endpoint = $endpoint;
  }

}
