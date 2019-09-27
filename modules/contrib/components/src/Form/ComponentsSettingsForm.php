<?php

/**
 * @file
 * Contains \Drupal\components\Form\ComponentSettingsForm.
 */

namespace Drupal\components\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure components settings for this site.
 */
class ComponentsSettingsForm extends ConfigFormBase {
    /** @var string Config settings */
  const SETTINGS = 'components.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'components_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $messenger = \Drupal::messenger();
    $messenger->addMessage(
      t('Be sure to update any theme or module namespaces before enabling.'),
      $messenger::TYPE_WARNING);

    $form['namespace_prefix'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prefix Module or Theme Name in Namespace'),
      '#default_value' => $config->get('namespace_prefix'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      // Retrieve the configuration
       $this->configFactory->getEditable(static::SETTINGS)
      ->set('namespace_prefix', $form_state->getValue('namespace_prefix'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
