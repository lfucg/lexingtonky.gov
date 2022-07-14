<?php

namespace Drupal\search_api_solr_devel\Logging;

use Drupal\Component\Utility\Timer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Serialization\Json;
use Drupal\devel\DevelDumperManagerInterface;
use Drupal\search_api\LoggerTrait;
use Drupal\search_api_solr\Utility\Utility;
use Solarium\Core\Client\Adapter\AdapterHelper;
use Solarium\Core\Event\Events;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to handle Solarium events.
 */
class SolariumRequestLogger implements EventSubscriberInterface {

  use StringTranslationTrait;
  use LoggerTrait;

  /**
   * The Devel dumper manager.
   *
   * @var \Drupal\devel\DevelDumperManagerInterface
   */
  protected $develDumperManager;

  /**
   * Constructs a ModuleRouteSubscriber object.
   *
   * @param \Drupal\devel\DevelDumperManagerInterface $develDumperManager
   *   The dump manager.
   */
  public function __construct(DevelDumperManagerInterface $develDumperManager) {
    $this->develDumperManager = $develDumperManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      Events::POST_CREATE_QUERY => 'postCreateQuery',
      Events::PRE_EXECUTE_REQUEST => 'preExecuteRequest',
      Events::POST_EXECUTE_REQUEST => 'postExecuteRequest',
    ];
  }

  /**
   * Dumps a Solr query as drupal messages.
   *
   * @param \Drupal\search_api_solr\Solarium\EventDispatcher\EventProxy $event
   *   The pre execute event.
   */
  public function postCreateQuery($event) {
    /** @var \Solarium\Core\Event\PostCreateQuery $event */
    $query = $event->getQuery();
    if ($query instanceof Query) {
      /** @var $query */
      $query->getDebug();
      $query->addParam('echoParams', 'all')
        ->setOmitHeader(FALSE);
    }
  }

  /**
   * Show debug message and a data object dump.
   *
   * @param $counter int
   *   The current Solr query counter.
   * @param $data mixed
   *   Data to dump.
   * @param $message string
   *   Message to show.
   */
  public function showMessage($counter, $data, $message) {
    $message = 'Request #' . $counter . '. ' . $message;
    $this->develDumperManager->message($data, $message, 'debug', 'kint');
  }

  /**
   * Start timer for a query.
   *
   * @param $counter int
   *   The current Solr query counter.
   */
  public function timerStart($counter) {
    Timer::start('search_api_solr_devel_' . $counter);
  }

  /**
   * Returns timer for a query.
   *
   * @param $counter int
   *   The current Solr query counter.
   * @return array
   *   The timer array.
   */
  public function timerStop($counter) {
    return Timer::stop('search_api_solr_devel_' . $counter);
  }

  /**
   * Determine which Solr requests should be ignored.
   *
   * @param $handler string
   *   The Solr handler. Examples: "admin/ping", "select", etc.
   * @return boolean
   *   TRUE when we should skip debugging this query.
   */
  public function shouldIgnore($handler) {
    $regex = '/.*admin.*/';
    return preg_match($regex, $handler);
  }

  /**
   * Dumps a Solr query as drupal messages.
   *
   * @param \Drupal\search_api_solr\Solarium\EventDispatcher\EventProxy $event
   *   The pre execute event.
   */
  public function preExecuteRequest($event) {
    static $counter = 0;
    $counter++;

    /** @var \Solarium\Core\Event\PreExecuteRequest $event */
    $request = $event->getRequest();
    $endpoint = $event->getEndpoint();

    if ($this->shouldIgnore($request->getHandler())) {
      return;
    }

    $debug = [
      'request count' => $counter,
      'datetime' => gmdate("Y-m-d\TH:i:sP"),
      'Solr request' => $request,
      'Solr endpoint' => $endpoint,
      'Solr URI' => AdapterHelper::buildUri($request, $endpoint),
    ];

    // Show debugging on page.
    $this->showMessage($counter, $debug, 'Search API Solr Debug: Request');

    // Log raw data to file.
    $this->develDumperManager->debug($debug, 'Search API Solr Debug: Request', 'default');
    $this->timerStart($counter);
  }

  /**
   * Dumps a Solr response status as drupal messages and logs the response body.
   *
   * @param \Drupal\search_api_solr\Solarium\EventDispatcher\EventProxy $event
   *   The post execute event.
   */
  public function postExecuteRequest($event) {
    static $counter = 0;
    $counter++;

    if ($this->shouldIgnore($event->getRequest()->getHandler())) {
      return;
    }

    $timer = $this->timerStop($counter);

    /** @var \Solarium\Core\Event\PostExecuteRequest $event */
    $response = $event->getResponse();

    $debug = [
      'request count' => $counter,
      'datetime' => gmdate("Y-m-d\TH:i:sP"),
      'query_time' => 'Solr query took ' . $timer['time'] . 'ms.',
      'Solr response headers' => $response->getHeaders(),
      'Solr response body' => $response->getBody(),
    ];

    // Show debugging on page.
    $this->showMessage($counter, $debug, 'Search API Solr Debug: Response');

    // Log raw data to file (using NULL plugin)
    $this->develDumperManager->debug($debug, 'Search API Solr Debug: Response', 'default');
  }
}
