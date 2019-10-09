<?php

namespace Drupal\autologout\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Automated Logout info' block.
 *
 * @Block(
 *   id = "autologout_warning_block",
 *   admin_label = @Translation("Automated logout info"),
 *   category = @Translation("User"),
 * )
 */
class AutologoutWarningBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The module manager service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The config object for 'autologout.settings'.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $autoLogoutSettings;

  /**
   * Constructs an AutologoutWarningBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Config\Config $autologout_settings
   *   The config object for 'autologout.settings'.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, DateFormatterInterface $date_formatter, Config $autologout_settings) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->dateFormatter = $date_formatter;
    $this->autoLogoutSettings = $autologout_settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('date.formatter'),
      $container->get('config.factory')->get('autologout.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // @todo: This is not the place where we should be doing this.
    $return = [];
    //if ($this->moduleHandler->moduleExists('jstimer')) {
    //  if (!$this->moduleHandler->moduleExists(('jst_timer'))) {
    //    drupal_set_message($this->t('The "Widget: timer" module must also be enabled for the dynamic countdown to work in the automated logout block.'), 'error');
    //  }

    //  if ($this->autoLogoutSettings->get('jstimer_js_load_option') != 1) {
    //    drupal_set_message($this->t("The Javascript timer module's 'Javascript load options' setting should be set to 'Every page' for the dynamic countdown to work in the automated logout block."), 'error');
    //  }
    //}
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $autologout_manager = \Drupal::service('autologout.manager');
    if ($autologout_manager->preventJs()) {

      // Don't display the block if the user is not going
      // to be logged out on this page.
      return [];
    }

    if ($autologout_manager->refreshOnly()) {
      $markup = $this->t('Autologout does not apply on the current page,
         you will be kept logged in whilst this page remains open.');
    }
    elseif ($this->moduleHandler->moduleExists('jstimer') && $this->moduleHandler->moduleExists('jst_timer')) {
      return \Drupal::formBuilder()->getForm('Drupal\autologout\Form\AutologoutBlockForm');
    }
    else {
      $timeout = (int) $this->autoLogoutSettings->get('timeout');
      $markup = $this->t('You will be logged out in @time if this page is not refreshed before then.', ['@time' => $this->dateFormatter->formatInterval($timeout)]);
    }

    return [
      '#type' => 'markup',
      '#markup' => $markup,
    ];
  }

}
