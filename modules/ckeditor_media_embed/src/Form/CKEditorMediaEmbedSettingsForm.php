<?php

namespace Drupal\ckeditor_media_embed\Form;

use Drupal\ckeditor_media_embed\AssetManager;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CKEditorMediaEmbedSettingsForm.
 *
 * @package Drupal\ckeditor_media_embed\Form
 */
class CKEditorMediaEmbedSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandler $module_handler, UrlGeneratorInterface $url_generator) {
    parent::__construct($config_factory);

    $this->urlGenerator = $url_generator;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ckeditor_media_embed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor_media_embed_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ckeditor_media_embed.settings');

    if (!AssetManager::pluginsAreInstalled()) {
      drupal_set_message(_ckeditor_media_embed_get_install_instructions(), 'warning');
      return array();
    }

    $form['embed_provider'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Provider URL'),
      '#default_value' => $config->get('embed_provider'),
      '#description' => $this->t('A template for the URL of the provider endpoint.
        This URL will be queried for each resource to be embedded. By default CKEditor uses the Iframely service.<br />
        <strong>Example</strong> <code>//example.com/api/oembed-proxy?resource-url={url}&callback={callback}&api_token=MYAPITOKEN</code><br />
        <strong>Default</strong> <code>//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}</code><br />
      '),
    );

    if ($this->moduleHandler->moduleExists('help')) {
      $form['embed_provider']['#description'] .= $this->t('Check out the <a href=":help">help</a> page for more information.<br />',
        array(':help' => $this->urlGenerator->generateFromRoute('help.page', array('name' => 'ckeditor_media_embed')))
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $embed_provider = $form_state->getValue('embed_provider');
    $this->prepareEmbedProviderValidation($embed_provider);

    if (!URLHelper::isValid($embed_provider, TRUE)) {
      $form_state->setErrorByName('embed_provider', $this->t('The provider url was not valid.'));
    }
  }

  /**
   * Prepare the embed provider setting for validation.
   *
   * @param string $embed_provider
   *   The embed provider that should be prepared for validation.
   *
   * @return $this
   */
  protected function prepareEmbedProviderValidation(&$embed_provider) {
    if (strpos($embed_provider, '//') === 0) {
      $embed_provider = 'http:' . $embed_provider;
    }

    $embed_provider = str_replace('{url}', '', $embed_provider);
    $embed_provider = str_replace('{callback}', '', $embed_provider);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ckeditor_media_embed.settings')
      ->set('embed_provider', $form_state->getValue('embed_provider'))
      ->save();
  }

}
