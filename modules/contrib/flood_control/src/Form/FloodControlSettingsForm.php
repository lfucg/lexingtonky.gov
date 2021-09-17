<?php

namespace Drupal\flood_control\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Administration settings form.
 */
class FloodControlSettingsForm extends ConfigFormBase {

  /**
   * The date formatter interface.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, DateFormatterInterface $dateFormatter, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->dateFormatter = $dateFormatter;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('date.formatter'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'flood_control_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['user.flood'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $flood_config = $this->config('user.flood');
    $flood_settings = $flood_config->get();

    $options = $this->getOptions();
    $counterOptions = $options['counter'];
    $timeOptions = $options['time'];

    // User module flood events.
    $form['login'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Login'),
    ];

    $form['login']['intro'] = [
      '#markup' => $this->t('The website blocks login attempts when the limit within a particular time window has been reached. The website records both attempts from IP addresses and usernames. When the limit is reached the user login form cannot be used anymore. You can remove blocked usernames and IP address from the <a href=":url">Flood Unblock page</a>.', [':url' => Url::fromRoute('flood_control.unblock_form')->toString()]),
    ];

    $form['login']['ip_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('IP login limit'),
      '#options' => array_combine($counterOptions, $counterOptions),
      '#default_value' => $flood_settings['ip_limit'],
      '#description' => $this->t('The allowed number of failed login attempts from one IP address within the allowed time window.'),
    ];

    $form['login']['ip_window'] = [
      '#type' => 'select',
      '#title' => $this->t('IP time window'),
      '#options' => [0 => $this->t('None (disabled)')] + array_map([
        $this->dateFormatter,
        'formatInterval',
      ], array_combine($timeOptions, $timeOptions)),
      '#default_value' => $flood_settings['ip_window'],
      '#description' => $this->t('The allowed time window for failed IP logins.'),
    ];
    $form['login']['user_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Username login limit'),
      '#options' => array_combine($counterOptions, $counterOptions),
      '#default_value' => $flood_settings['user_limit'],
      '#description' => $this->t('The allowed number of failed login attempts with one username within the allowed time window.'),
    ];
    $form['login']['user_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Username login time window'),
      '#options' => [0 => $this->t('None (disabled)')] + array_map([
        $this->dateFormatter,
        'formatInterval',
      ], array_combine($timeOptions, $timeOptions)),
      '#default_value' => $flood_settings['user_window'],
      '#description' => $this->t('The allowed time window for failed username logins.'),
    ];

    // Contact module flood events.
    if ($this->moduleHandler->moduleExists('contact')) {
      $contact_config = $this->config('contact.settings');
      $contact_settings = $contact_config->get();
      $form['contact'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Contact forms'),
      ];
      $form['contact']['intro'] = [
        '#markup' => $this->t('The website blocks contact form submissions when the limit within a particular time window has been reached.'),
      ];
      $form['contact']['contact_threshold_limit'] = [
        '#type' => 'select',
        '#title' => $this->t('Sending e-mails limit'),
        '#options' => array_combine($counterOptions, $counterOptions),
        '#default_value' => $contact_settings['flood']['limit'],
        '#description' => $this->t('The allowed number of submissions within the allowed time window.'),
      ];
      $form['contact']['contact_threshold_window'] = [
        '#type' => 'select',
        '#title' => $this->t('Sending e-mails window'),
        '#options' => [0 => $this->t('None (disabled)')] + array_map([
          $this->dateFormatter,
          'formatInterval',
        ], array_combine($timeOptions, $timeOptions)),
        '#default_value' => $contact_settings['flood']['interval'],
        '#description' => $this->t('The allowed time window for contact form submissions.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $flood_config = $this->configFactory->getEditable('user.flood');
    $flood_config
      ->set('ip_limit', $form_state->getValue('ip_limit'))
      ->set('ip_window', $form_state->getValue('ip_window'))
      ->set('user_limit', $form_state->getValue('user_limit'))
      ->set('user_window', $form_state->getValue('user_window'))
      ->save();

    if ($this->moduleHandler->moduleExists('contact')) {
      $contact_config = $this->configFactory->getEditable('contact.settings');
      $contact_config
        ->set('flood.limit', $form_state->getValue('contact_threshold_limit'))
        ->set('flood.interval', $form_state->getValue('contact_threshold_window'))
        ->save();
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Provides options for the select lists.
   */
  protected function getOptions() {
    return [
      'counter' => [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        20,
        30,
        40,
        50,
        75,
        100,
        125,
        150,
        200,
        250,
        500,
      ],
      'time' => [
        60,
        180,
        300,
        600,
        900,
        1800,
        2700,
        3600,
        10800,
        21600,
        32400,
        43200,
        86400,
      ],
    ];
  }

}
