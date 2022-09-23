<?php

namespace Drupal\search_api_pantheon\Services;

use Psr\Log\LoggerAwareTrait;
use Solarium\Core\Client\Client;
use Solarium\Core\Client\Request;
use Solarium\Core\Client\Response;
use Solarium\Core\Query\QueryInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\search_api_pantheon\Solarium\EventDispatcher\Psr14Bridge;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Customized Solrium Client to send Guzzle debugging to log entries.
 */
class SolariumClient extends Client {
  use LoggerAwareTrait;

  /**
   * Class constructor.
   */
  public function __construct(PantheonGuzzle $guzzle, Endpoint $endpoint, LoggerChannelFactoryInterface $logger_factory, ContainerAwareEventDispatcher $event_dispatcher) {
    $drupal_major_parts = explode('.', \Drupal::VERSION);
    $drupal_major = reset($drupal_major_parts);
    if ($drupal_major < 9) {
      // Use the bridge only if Drupal 8.
      $event_dispatcher = new Psr14Bridge($event_dispatcher);
    }
    parent::__construct(
          $guzzle->getPsr18Adapter(),
          $event_dispatcher,
          ['endpoint' => [$endpoint]]
      );
    $this->logger = $logger_factory->get('PantheonSolariumClient');
    $this->setDefaultEndpoint($endpoint);
  }

  /**
   * Always use the default endpoint.
   *
   * @param \Solarium\Core\Query\QueryInterface $query
   * @param \Solarium\Core\Client\Endpoint|string|null $endpoint
   *
   * @return \Solarium\Core\Query\Result\ResultInterface
   */
  public function execute(QueryInterface $query, $endpoint = NULL): ResultInterface {
    return parent::execute($query, $this->defaultEndpoint);
  }

  /**
   * Always use the default endpoint.
   *
   * @param \Solarium\Core\Client\Request $request
   * @param \Solarium\Core\Client\Endpoint|string|null $endpoint
   *
   * @return \Solarium\Core\Client\Response
   */
  public function executeRequest(Request $request, $endpoint = NULL): Response {
    return parent::executeRequest($request, $this->defaultEndpoint);
  }

}
