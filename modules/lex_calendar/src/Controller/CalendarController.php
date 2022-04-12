<?php

namespace Drupal\lex_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
// use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\lex_calendar\EventFetch;
use Drupal\lex_calendar\FullCalendarService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * JSON Responder for fullcalendar.js.
 */
class CalendarController extends ControllerBase {

  use EventFetch;

  /**
   * Response object.
   *
   * @var Symfony\Component\HttpFoundation\JsonResponse object
   */
  protected $response = NULL;

  /**
   * Request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack object
   */
  protected $requestStack = NULL;

  /**
   * Default Drupal Entity Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager object
   */
  protected $entityTypeManager = NULL;

  /**
   * Constructs a CalendarController object.
   *
   * @param Drupal\lex_calendar\FullCalendarService $events
   *   Custom event managing service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Manager service.
   * @param Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request Stack.
   * @param Symfony\Component\HttpFoundation\JsonResponse $response
   *   Response.
   */
  public function __construct(FullCalendarService $events, EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack, JsonResponse $response) {
    $this->events = $events;
    // $this->getQuery = $getQuery;
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
    $this->response = $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lex_calendar.full_calendar'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      new JsonResponse()
    );
  }

  /**
   * Responder for route /calendar/fetchMeetings.
   */
  public function fetchMeetings() {
    return $this->jsonFetch('meeting');
  }

  /**
   * Responder for route /calendar/fetchEventsAndMeetings.
   */
  public function fetchEventsAndMeetings() {
    return $this->jsonFetch(['event', 'meeting']);
  }

  /**
   * Compose and return a JSON object of calendar data.
   *
   * @param array $contentTypes
   *   The Content Type to fetch, for now mettings or events.
   */
  protected function jsonFetch($contentTypes) {
     foreach($contentTypes as $ct) { 
      /*
      * Load the date range to search into the FullCalendar service.
      */
      $request = $this->requestStack->getCurrentRequest();
      $this->queryEvents($ct, $request->query->get('start'), $request->query->get('end'));

      /*
      * Pack the results up and send them out the door.
      */
      $this->response->setData($this->events->getEvents());
    }
    return $this->response;
  }
}
