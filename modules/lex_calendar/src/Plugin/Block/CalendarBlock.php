<?php

namespace Drupal\lex_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\lex_calendar\EventFetch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\lex_calendar\FullCalendarService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract Event list Block for the home page.
 */
abstract class CalendarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EventFetch;

  protected $contentType = NULL;

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
   * Constructs a CalendarController object.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Block machine id.
   * @param array $plugin_definition
   *   Block definition from annotation.
   * @param Drupal\lex_calendar\FullCalendarService $events
   *   Custom event managing service.
   * @param Drupal\Core\Entity\Query\QueryFactory $entityQuery
   *   Query prep service.
   * @param Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   Entity Manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FullCalendarService $events, QueryFactory $entityQuery, EntityManagerInterface $entityManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->events = $events;
    $this->entityQuery = $entityQuery;
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config = [], $plugin_id = '', $plugin_definition = '') {
    return new static(
      $config,
      $plugin_id,
      $plugin_definition,
      $container->get('lex_calendar.full_calendar'),
      $container->get('entity.query'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->events->clear();
    $this->queryEvents($this->contentType,
      new \DateTime('now', new \DateTimeZone('America/New_York')),
      new \DateTime('+1 month', new \DateTimeZone('America/New_York'))
    );

    $this->events->sort();

    $events = array_chunk($this->events->getEvents(), 3)[0];

    $dates = [];

    foreach ($events as $event) {
      $start_day = explode(' ', $event['start'])[0];

      if (!isset($dates[$start_day])) {
        $dates[$start_day] = [
          'title' => $start_day,
          'events' => []
        ];
      }

      $dates[$start_day]['events'][] = $event;
    }

    return [
      '#theme' => 'lex_calendar_event_block',
      '#dates' => $dates,
      '#content_type' => $this->contentType,
      '#cache' => ['max-age' => 1200]
    ];
  }
}
