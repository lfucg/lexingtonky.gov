<?php

namespace Drupal\autologout\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides settings for autologout module.
 */
class AutologoutSettingsForm extends ConfigFormBase {

  /**
   * The module manager service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs an AutologoutSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['autologout.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'autologout_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('autologout.settings');
    $form['timeout'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Timeout value in seconds'),
      '#default_value' => $config->get('timeout'),
      '#size' => 8,
      '#weight' => -10,
      '#description' => $this->t('The length of inactivity time, in seconds, before automated log out.  Must be 60 seconds or greater. Will not be used if role timeout is activated.'),
    ];

    $form['max_timeout'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max timeout setting'),
      '#default_value' => $config->get('max_timeout'),
      '#size' => 10,
      '#maxlength' => 12,
      '#weight' => -8,
      '#description' => $this->t('The maximum logout threshold time that can be set by users who have the permission to set user level timeouts.'),
    ];

    $form['padding'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Timeout padding'),
      '#default_value' => $config->get('padding'),
      '#size' => 8,
      '#weight' => -6,
      '#description' => $this->t('How many seconds to give a user to respond to the logout dialog before ending their session.'),
    ];

    $form['role_logout'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Role Timeout'),
      '#default_value' => $config->get('role_logout'),
      '#weight' => -4,
      '#description' => $this->t('Enable each role to have its own timeout threshold, a refresh maybe required for changes to take effect. Any role not ticked will use the default timeout value. Any role can have a value of 0 which means that they will never be logged out.'),
    ];

    $form['redirect_url']  = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URL at logout'),
      '#default_value' => $config->get('redirect_url'),
      '#size' => 40,
      '#description' => $this->t('Send users to this internal page when they are logged out.'),
    ];

    $form['no_dialog'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Do not display the logout dialog'),
      '#default_value' => $config->get('no_dialog'),
      '#description' => $this->t('Enable this if you want users to logout right away and skip displaying the logout dialog.'),
    ];

    $form['use_alt_logout_method'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use alternate logout method'),
      '#default_value' => $config->get('use_alt_logout_method'),
      '#description' => $this->t('Normally when auto logout is triggered, it is done via an AJAX service call. Sites that use an SSO provider, such as CAS, are likely to see this request fail with the error "Origin is not allowed by Access-Control-Allow-Origin". The alternate approach is to have the auto logout trigger a page redirect to initiate the logout process instead.'),
    ];

    $form['message']  = [
      '#type' => 'textarea',
      '#title' => $this->t('Message to display in the logout dialog'),
      '#default_value' => $config->get('message'),
      '#size' => 40,
      '#description' => $this->t('This message must be plain text as it might appear in a JavaScript confirm dialog.'),
    ];

    $form['inactivity_message']  = [
      '#type' => 'textarea',
      '#title' => $this->t('Message to display to the user after they are logged out.'),
      '#default_value' => $config->get('inactivity_message'),
      '#size' => 40,
      '#description' => $this->t('This message is displayed after the user was logged out due to inactivity. You can leave this blank to show no message to the user.'),
    ];

    $form['use_watchdog'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable watchdog Automated Logout logging'),
      '#default_value' => $config->get('use_watchdog'),
      '#description' => $this->t('Enable logging of automatically logged out users'),
    ];

    $form['enforce_admin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enforce auto logout on admin pages'),
      '#default_value' => $config->get('enforce_admin'),
      '#description' => $this->t('If checked, then users will be automatically logged out when administering the site.'),
    ];

    if ($this->moduleHandler->moduleExists('jstimer') && $this->moduleHandler->moduleExists('jst_timer')) {
      $form['jstimer_format']  = [
        '#type' => 'textfield',
        '#title' => $this->t('Autologout block time format'),
        '#default_value' => $config->get('jstimer_format'),
        '#description' => $this->t('Change the display of the dynamic timer.  Available replacement values are: %day%, %month%, %year%, %dow%, %moy%, %years%, %ydays%, %days%, %hours%, %mins%, and %secs%.'),
      ];
    }

    $form['table'] = [
      '#type' => 'table',
      '#weight' => -2,
      '#header' => [
        'enable' => $this->t('Enable'),
        'name' => $this->t('Role Name'),
        'timeout' => $this->t('Timeout (seconds)'),
      ],
      '#title' => $this->t('If Enabled every user in role will be logged out based on that roles timeout, unless the user has an individual timeout set.'),
      '#states' => [
        'visible' => [
          // Only show this field when the 'role_logout' checkbox is enabled.
          ':input[name="role_logout"]' => ['checked' => TRUE],
        ],
      ],
    ];

    foreach (user_roles(TRUE) as $key => $role) {
      $form['table'][$key] = [
        'enabled' => [
          '#type' => 'checkbox',
          '#default_value' => $this->config('autologout.role.' . $key)->get('enabled'),
        ],
        'role' => [
          '#type' => 'item',
          '#value' => $key,
          '#markup' => $key,
        ],
        'timeout' => [
          '#type' => 'textfield',
          '#default_value' => $this->config('autologout.role.' . $key)->get('timeout'),
          '#size' => 8,
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }
  /**
   * Validate timeout range.
   *
   * Checks to see if timeout threshold is outside max/min values. Done here
   * to centralize and stop repeated code. Hard coded min, configurable max.
   *
   * @param int $timeout
   *   The timeout value in seconds to validate.
   * @param int $max_timeout
   *   (optional) Maximum value of timeout. If not set, system default is used.
   *
   * @return bool
   *    Return TRUE or FALSE
   */
  public function timeoutValidate($timeout, $max_timeout = NULL) {
    $validate = TRUE;
    if (is_null($max_timeout)) {
      $max_timeout = $this->config('autologout.settings')->get('max_timeout');
    }

    if (!is_numeric($timeout) || $timeout < 0 || ($timeout > 0 && $timeout < 60) || $timeout > $max_timeout) {
      // Less than 60, greater than max_timeout and is numeric.
      // 0 is allowed now as this means no timeout.
      $validate = FALSE;
    }
    return $validate;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $new_stack = [];
    foreach ($values['table'] as $key => $pair) {
      if (is_array($pair)) {
        foreach ($pair as $pairkey => $pairvalue) {
          $new_stack[$key][$pairkey] = $pairvalue;
        }
      }
    }

    $max_timeout = $values['max_timeout'];

    if ($values['role_logout']) {
      // Validate timeouts for each role.
      foreach (array_keys(user_roles(TRUE)) as $role) {
        if (empty($new_stack[$role]) || $new_stack[$role]['enabled'] == 0) {
          // Don't validate role timeouts for non enabled roles.
          continue;
        }

        $timeout = $new_stack[$role]['timeout'];
        $validate = $this->timeoutValidate($timeout, $max_timeout);
        if (!$validate) {
          $form_state->setErrorByName('table][' . $role . '][timeout', $this->t('%role role timeout must be an integer greater than 60, less then %max or 0 to disable autologout for that role.', ['%role' => $role, '%max' => $max_timeout]));
        }
      }
    }

    $timeout = $values['timeout'];
    // Validate timeout.
    if ($timeout < 60) {
      $form_state->setErrorByName('timeout', $this->t('The timeout value must be an integer 60 seconds or greater.'));
    }
    elseif ($max_timeout <= 60) {
      $form_state->setErrorByName('max_timeout', $this->t('The max timeout must be an integer greater than 60.'));
    }
    elseif (!is_numeric($timeout) || ((int) $timeout != $timeout) || $timeout < 60 || $timeout > $max_timeout) {
      $form_state->setErrorByName('timeout', $this->t('The timeout must be an integer greater than 60 and less then %max.', ['%max' => $max_timeout]));
    }

    $redirect_url = $values['redirect_url'];

    // Validate redirect url.
    if (strpos($redirect_url, '/') !== 0) {
      $form_state->setErrorByName('redirect_url', $this->t("The user-entered string :redirect_url must begin with a '/'", [':redirect_url' => $redirect_url]));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $autologout_settings = $this->config('autologout.settings');

    $autologout_settings->set('timeout', $values['timeout'])
      ->set('max_timeout', $values['max_timeout'])
      ->set('padding', $values['padding'])
      ->set('role_logout', $values['role_logout'])
      ->set('redirect_url', $values['redirect_url'])
      ->set('no_dialog', $values['no_dialog'])
      ->set('message', $values['message'])
      ->set('inactivity_message', $values['inactivity_message'])
      ->set('enforce_admin', $values['enforce_admin'])
      ->set('use_alt_logout_method', $values['use_alt_logout_method'])
      ->set('use_watchdog', $values['use_watchdog'])
      ->save();

    foreach ($values['table'] as $user) {
      $this->configFactory()->getEditable('autologout.role.' . $user['role'])
        ->set('enabled', $user['enabled'])
        ->set('timeout', $user['timeout'])
        ->save();
    }

    if (isset($values['jstimer_format'])) {
      $autologout_settings->set('jstimer_format', $values['jstimer_format'])->save();
    }

    parent::submitForm($form, $form_state);
  }

}
