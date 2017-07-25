<?php

namespace Drupal\lex_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\lex_calendar\EventFetch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\lex_calendar\FullCalendarService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Calendar Event list block.
 *
 * @Block(
 *   id = "Lex_calendar_list_block",
 *   admin_label = @Translation("Lex Calendar List Block")
 * )
 */
class CalendarBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

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
   * Current Route.
   *
   * @var Drupal\Core\Routing\RouteMatchInterface object
   */
  protected $routeMatch = NULL;

  /**
   * Office to Contact or Related Department for page.
   */
  protected $targetDepartment = NULL;

  /**
   * Field to filter the above.
   */
  protected $filterField = NULL;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FullCalendarService $events, QueryFactory $entityQuery, EntityManagerInterface $entityManager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->events = $events;
    $this->entityQuery = $entityQuery;
    $this->entityManager = $entityManager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['display_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Item Limit'),
      '#description' => $this->t('How many items do you wish to show?'),
      '#default_value' => isset($config['display_limit']) ? $config['display_limit'] : 3
    ];

    $form['content_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Content Type'),
      '#options' => [
        'event' => $this->t('Events'), 
        'meeting' => $this->t('Meetings')
      ],
      '#default_value' => isset($config['content_type']) ? $config['content_type'] : '',
      '#required' => TRUE
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $config = $this->getConfiguration();
    $values = $form_state->getValues();
    $this->configuration['display_limit'] = $values['display_limit'];
    $this->configuration['content_type'] = $values['content_type'];
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
      $container->get('entity.manager'),
      $container->get('current_route_match')
    );
  }

  protected function setQueryModification() {
    $entity = $this->routeMatch->getParameter('node');

    try {
      $related = $entity->get('field_organization_taxonomy_term')->getValue();

      if (!empty($related)) {
        $this->filterField = 'field_related_departments';
        $this->targetDepartment = $related[0]['target_id'];
      }
    } 
    /*
     * On pages that don't have related departments or offices to contact,
     * such as the home page, we simply proceed without filling out these
     * fields. This results in a query of all possible meetings or events.
     */
    catch ( \InvalidArgumentException $e ) {}
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $this->contentType = $config['content_type'];

    if (!$this->contentType) {
      throw new \Exception('Invalid configuration for Lex Calendar Block');
    }

    $this->setQueryModification();

    $this->events->clear();
    $this->queryEvents($this->contentType,
      new \DateTime('now', new \DateTimeZone('America/New_York')),
      new \DateTime('+1 month', new \DateTimeZone('America/New_York'))
    );

    $this->events->sort();

    $events = $this->events->getEvents();

    if (count($events) > 0) {
      $events = array_chunk($events, $config['display_limit'])[0];

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
        # Force Git to detect change to next line.
        '#cache' => ['max-age' => 0]
      ];
    }
    else {
      return [];
    }
  }

  protected function modifyEventQuery($query) {
    return $this->targetDepartment ? 
      $query->condition($this->filterField, $this->targetDepartment) : 
      $query;
  }
}
