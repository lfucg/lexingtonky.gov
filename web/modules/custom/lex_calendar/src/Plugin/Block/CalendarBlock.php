<?php

namespace Drupal\lex_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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

  /**
   * Content Type.
   *
   * @var string
   */
  protected $contentType = NULL;

  /**
   * Default Drupal Entity Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager = NULL;

  /**
   * Current Route.
   *
   * @var Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch = NULL;

  /**
   * Office to Contact or Related Department for page.
   *
   * @var mixed
   */
  protected $targetDepartment = NULL;

  /**
   * Page of Contact or Related Department.
   *
   * @var mixed
   */
  protected $targetPage = NULL;

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
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Manager service.
   * @param Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Entity Manager service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, FullCalendarService $events, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->events = $events;
    $this->entityTypeManager = $entityTypeManager;
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
      '#default_value' => $config['display_limit'] ?? 3,
    ];

    $form['content_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Content Type'),
      '#options' => [
        'event' => $this->t('Events'),
        'meeting' => $this->t('Meetings'),
      ],
      '#default_value' => $config['content_type'] ?? '',
      '#required' => TRUE,
    ];

    $form['show_all'] = [
      '#type' => 'radios',
      '#title' => $this->t('Show All Events'),
      '#description' => $this->t('If there is no related department, do we show all possible events?'),
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#default_value' => $config['show_all'] ?? 'no',
      '#required' => TRUE,
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

    foreach (['display_limit', 'content_type', 'show_all'] as $key) {
      $this->configuration[$key] = $values[$key];
    }
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
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setQueryModification() {
    $entity = $this->routeMatch->getParameter('node');
    $entype = $entity->getType();
    $entid = $entity->nid->value;
    try {
      $related = $entity->get('field_organization_taxonomy_term')->getValue();
    }
    catch (\InvalidArgumentException $e) {
      try {
        $related = $entity->get('field_related_departments')->getValue();
      }
      catch (\InvalidArgumentException $e) {

      }
    }

    if (!empty($related) && $entype != 'page') {
      $this->targetDepartment = $related[0]['target_id'];
    }
    elseif (empty($related) && $entype == 'page') {
      $this->targetPage = $entid;
    }
    elseif (!empty($related) && $entype == 'page') {
      $this->targetPage = $entid;
    }
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

    if ($config['show_all'] === 'no' && empty($this->targetDepartment) && empty($this->targetPage)) {
      return ['#cache' => ['max-age' => 0]];
    }

    $this->events->clear();
    $this->queryEvents($this->contentType,
      new \DateTime('now', new \DateTimeZone('America/New_York')),
      new \DateTime('+3 months', new \DateTimeZone('America/New_York')),
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
            'events' => [],
          ];
        }

        $dates[$start_day]['events'][] = $event;
      }

      return [
        '#theme' => 'lex_calendar_event_block',
        '#dates' => $dates,
        '#content_type' => $this->contentType,
        '#is_front' => \Drupal::service('path.matcher')->isFrontPage(),
        '#cache' => ['max-age' => 0],
      ];
    }
    else {
      return ['#cache' => ['max-age' => 0]];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function modifyEventQuery($query) {
    if ($this->targetPage == '13197') {
      return $query;
    }
    if ($this->targetPage) {
      return $query->condition('field_related_page', $this->targetPage);
    }
    elseif ($this->targetDepartment) {
      return $query->condition('field_related_departments', $this->targetDepartment);
    }
    else {
      return $query;
    }
  }

}
