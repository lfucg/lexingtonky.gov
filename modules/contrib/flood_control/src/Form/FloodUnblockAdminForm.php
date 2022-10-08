<?php

namespace Drupal\flood_control\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\flood_control\FloodUnblockManager;
use Drupal\Core\Session\AccountProxy;

/**
 * Admin form of Flood Unblock.
 */
class FloodUnblockAdminForm extends FormBase {

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The FloodUnblockManager service.
   *
   * @var \Drupal\flood_control\FloodUnblockManager
   */
  protected $floodUnblockManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * User flood config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $userFloodConfig;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * FloodUnblockAdminForm constructor.
   *
   * @param \Drupal\flood_control\FloodUnblockManager $floodUnblockManager
   *   The FloodUnblockManager service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   The current user service.
   */
  public function __construct(FloodUnblockManager $floodUnblockManager, Connection $database, DateFormatterInterface $date_formatter, AccountProxy $currentUser) {
    $this->floodUnblockManager = $floodUnblockManager;
    $this->database = $database;
    $this->dateFormatter = $date_formatter;
    $this->userFloodConfig = $this->configFactory()->get('user.flood');
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flood_control.flood_unblock_manager'),
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'flood_unblock_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Fetches the limit from the form.
    $limit = $form_state->getValue('limit') ?? 33;

    // Fetches the identifier from the form.
    $identifier = $form_state->getValue('identifier');

    // Set default markup.
    $top_markup = $this->t("List of IP addresses and user ID's that are blocked after multiple failed login attempts. You can remove separate entries.");

    // Add link to control settings page if current user haas
    // permission to access it.
    if ($this->currentUser->hasPermission('access flood control settings page')) {
      $top_markup .= $this->t("You can configure the login attempt limits and time windows on the <a href=':url'>Flood Control settings page</a>.</p>", [':url' => Url::fromRoute('flood_control.settings')->toString()]);
    }

    // Provides introduction to the table.
    $form['top_markup'] = [
      '#markup' => "<p> {$top_markup} </p>",
    ];

    // Provides table filters.
    $form['filter'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter'),
      '#open' => FALSE,
      'limit' => [
        '#type' => 'number',
        '#title' => $this->t('Amount'),
        '#description' => $this->t("Number of lines shown in table."),
        '#size' => 5,
        '#min' => 1,
        '#steps' => 10,
        '#default_value' => $limit,
      ],
      'identifier' => [
        '#type' => 'textfield',
        '#title' => $this->t('Identifier'),
        '#default_value' => $identifier,
        '#size' => 20,
        '#description' => $this->t('(Part of) identifier: IP address or UID'),
        '#maxlength' => 256,
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Filter'),
      ],
    ];

    // Provides header for tableselect element.
    $header = [
      'identifier' => [
        'data' => $this->t('Identifier'),
        'field' => 'identifier',
        'sort' => 'asc',
      ],
      'blocked' => $this->t('Status'),
      'event' => [
        'data' => $this->t('Event'),
        'field' => 'event',
        'sort' => 'asc',
      ],
      'timestamp' => [
        'data' => $this->t('Timestamp'),
        'field' => 'timestamp',
        'sort' => 'asc',
      ],
      'expiration' => [
        'data' => $this->t('Expiration'),
        'field' => 'expiration',
        'sort' => 'asc',
      ],
    ];

    $options = [];

    // Fetches items from flood table.
    if ($this->database->schema()->tableExists('flood')) {
      $query = $this->database->select('flood', 'f')
        ->extend('Drupal\Core\Database\Query\TableSortExtender')
        ->orderByHeader($header);
      $query->fields('f');
      if ($identifier) {
        $query->condition('identifier', "%" . $this->database->escapeLike($identifier) . "%", 'LIKE');
      }
      $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
        ->limit($limit);
      $execute = $pager->execute();
      $results = $execute->fetchAll();
      $results_identifiers = array_column($results, 'identifier', 'fid');

      // Fetches user names or location string for identifiers.
      $identifiers = $this->floodUnblockManager->fetchIdentifiers(array_unique($results_identifiers));

      foreach ($results as $result) {

        // Gets status of identifier.
        $is_blocked = $this->floodUnblockManager->isBlocked($result->identifier, $result->event);

        // Defines list of options for tableselect element.
        $options[$result->fid] = [
          'identifier' => $identifiers[$result->identifier],
          'blocked' => $is_blocked ? $this->t('Blocked') : $this->t('Not blocked'),
          'event' => $this->floodUnblockManager->getEventLabel($result->event),
          'timestamp' => $this->dateFormatter->format($result->timestamp, 'short'),
          'expiration' => $this->dateFormatter->format($result->expiration, 'short'),
        ];
      }
    }

    // Provides the tableselect element.
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('There are no failed logins at this time.'),
    ];

    // Provides the remove submit button.
    $form['remove'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove selected items from the flood table'),
      '#validate' => ['::validateRemoveItems'],
    ];
    if (count($options) == 0) {
      $form['remove']['#disabled'] = TRUE;
    }

    // Provides the pager element.
    $form['pager'] = [
      '#type' => 'pager',
    ];

    $form['#cache'] = [
      'tags' => $this->userFloodConfig->getCacheTags(),
    ];
    return $form;
  }

  /**
   * Validates that items have been selected for removal.
   */
  public function validateRemoveItems(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $entries = $form_state->getValue('table');
    $selected_entries = array_filter($entries, function ($selected) {
      return $selected !== 0;
    });
    if (empty($selected_entries)) {
      $form_state->setErrorByName('table', $this->t('Please make a selection.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('table') as $fid) {
      if ($fid !== 0) {
        $this->floodUnblockManager->floodUnblockClearEvent($fid);
      }
    }
    $form_state->setRebuild();
  }

}
