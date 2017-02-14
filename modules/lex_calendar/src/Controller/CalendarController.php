<?php

namespace Drupal\lex_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\lex_calendar\FullCalendarService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * JSON Responder for fullcalendar.js.
 */
class CalendarController extends ControllerBase {

  /**
   * Custom service to translate Drupal nodes to full calendar event data.
   *
   * @var Drupal\lex_calendar\FullCalendarService object
   */
  protected $events = NULL;

  /**
   * Base Drupal Query Factory.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory object
   */
  protected $entityQuery = NULL;

  /**
   * Default Drupal Entity Manager.
   *
   * @var Drupal\Core\Entity\EntityManager object
   */
  protected $entityManager = NULL;

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
   * Constructs a CalendarController object.
   *
   * @param Drupal\lex_calendar\FullCalendarService $events
   *   Custom event managing service.
   * @param Drupal\Core\Entity\Query\QueryFactory $entityQuery
   *   Query prep service.
   * @param Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   Entity Manager service.
   * @param Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request Stack.
   * @param Symfony\Component\HttpFoundation\JsonResponse $response
   *   Response.
   */
  public function __construct(FullCalendarService $events, QueryFactory $entityQuery, EntityManagerInterface $entityManager, RequestStack $requestStack, JsonResponse $response) {
    $this->events = $events;
    $this->entityQuery = $entityQuery;
    $this->entityManager = $entityManager;
    $this->requestStack = $requestStack;
    $this->response = $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lex_calendar.full_calendar'),
      $container->get('entity.query'),
      $container->get('entity.manager'),
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
   * Responder for route /calendar/fetchEvents.
   */
  public function fetchEvents() {
    return $this->jsonFetch('event');
  }

  /**
   * Compose and return a JSON object of calendar data.
   *
   * @param string $contentType
   *   The Content Type to fetch, for now mettings or events.
   */
  protected function jsonFetch($contentType) {
    /*
     * Load the date range to search into the FullCalendar service.
     */
    $request = $this->requestStack->getCurrentRequest();
    $this->events->setStart($request->query->get('start'));
    $this->events->setEnd($request->query->get('end'));

    /*
     * Get the non recurring events for the range.
     */
    $query = $this->entityQuery->get('node')
      ->condition('status', 1)
      ->condition('type', $contentType)
      ->condition('field_date', $this->events->getStart()->format('Y-m-d'), '>=');

    $this->events->addEvents($this->entityManager()->getStorage('node')->loadMultiple($query->execute()));

    /*
     * And now the recurring events, which are handled by a seperate process
     * which duplicates them according to need.
     */
    $query = $this->entityQuery->get('node')
      ->condition('status', 1)
      ->condition('type', $contentType)
      ->condition('field_recurring_event', ['Weekly', 'Monthly'], 'IN');

    $this->events->addRecurringEvents($this->entityManager()->getStorage('node')->loadMultiple($query->execute()));

    /*
     * Pack the results up and send them out the door.
     */
    $this->response->setData($this->events->getEvents());
    return $this->response;
  }

}
