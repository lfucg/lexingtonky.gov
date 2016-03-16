<?php

/**
 * @file
 * Contains Drupal\honeypot\Controller\HoneypotSettingsController.
 */

namespace Drupal\honeypot\Controller;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\comment\Entity\CommentType;

/**
 * Returns responses for Honeypot module routes.
 */
class HoneypotSettingsController extends ConfigFormBase {

  /**
   * Get a value from the retrieved form settings array.
   */
  public function getFormSettingsValue($form_settings, $form_id) {
    // If there are settings in the array and the form ID already has a setting,
    // return the saved setting for the form ID.
    if (!empty($form_settings) && isset($form_settings[$form_id])) {
      return $form_settings[$form_id];
    }
    // Default to false.
    else {
      return 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['honeypot.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'honeypot_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Honeypot Configuration.
    $form['configuration'] = [
      '#type' => 'fieldset',
      '#title' => t('Honeypot Configuration'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['configuration']['protect_all_forms'] = [
      '#type' => 'checkbox',
      '#title' => t('Protect all forms with Honeypot'),
      '#description' => t('Enable Honeypot protection for ALL forms on this site (it is best to only enable Honeypot for the forms you need below).'),
      '#default_value' => $this->config('honeypot.settings')->get('protect_all_forms'),
    ];
    $form['configuration']['protect_all_forms']['#description'] .= '<br />' . t('<strong>Page caching will be disabled on any page where a form is present if the Honeypot time limit is not set to 0.</strong>');
    $form['configuration']['log'] = [
      '#type' => 'checkbox',
      '#title' => t('Log blocked form submissions'),
      '#description' => t('Log submissions that are blocked due to Honeypot protection.'),
      '#default_value' => $this->config('honeypot.settings')->get('log'),
    ];
    $form['configuration']['element_name'] = [
      '#type' => 'textfield',
      '#title' => t('Honeypot element name'),
      '#description' => t("The name of the Honeypot form field. It's usually most effective to use a generic name like email, homepage, or link, but this should be changed if it interferes with fields that are already in your forms. Must not contain spaces or special characters."),
      '#default_value' => $this->config('honeypot.settings')->get('element_name'),
      '#required' => TRUE,
      '#size' => 30,
    ];
    $form['configuration']['time_limit'] = [
      '#type' => 'textfield',
      '#title' => t('Honeypot time limit'),
      '#description' => t('Minimum time required before form should be considered entered by a human instead of a bot. Set to 0 to disable.'),
      '#default_value' => $this->config('honeypot.settings')->get('time_limit'),
      '#required' => TRUE,
      '#size' => 5,
      '#field_suffix' => t('seconds'),
    ];
    $form['configuration']['time_limit']['#description'] .= '<br />' . t('<strong>Page caching will be disabled if there is a form protected by time limit on the page.</strong>');

    // Honeypot Enabled forms.
    $form_settings = $this->config('honeypot.settings')->get('form_settings');
    $form['form_settings'] = [
      '#type' => 'fieldset',
      '#title' => t('Honeypot Enabled Forms'),
      '#description' => t("Check the boxes next to individual forms on which you'd like Honeypot protection enabled."),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
      '#states' => [
        // Hide this fieldset when all forms are protected.
        'invisible' => [
          'input[name="protect_all_forms"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Generic forms.
    $form['form_settings']['general_forms'] = ['#markup' => '<h5>' . t('General Forms') . '</h5>'];
    // User register form.
    $form['form_settings']['user_register_form'] = [
      '#type' => 'checkbox',
      '#title' => t('User Registration form'),
      '#default_value' => $this->getFormSettingsValue($form_settings, 'user_register_form'),
    ];
    // User password form.
    $form['form_settings']['user_pass'] = [
      '#type' => 'checkbox',
      '#title' => t('User Password Reset form'),
      '#default_value' => $this->getFormSettingsValue($form_settings, 'user_pass'),
    ];

    // If webform.module enabled, add webforms.
    // TODO D8 - See if D8 version of Webform.module still uses this form ID.
    if (\Drupal::moduleHandler()->moduleExists('webform')) {
      $form['form_settings']['webforms'] = [
        '#type' => 'checkbox',
        '#title' => t('Webforms (all)'),
        '#default_value' => $this->getFormSettingsValue($form_settings, 'webforms'),
      ];
    }

    // If contact.module enabled, add contact forms.
    if (\Drupal::moduleHandler()->moduleExists('contact')) {
      $form['form_settings']['contact_forms'] = ['#markup' => '<h5>' . t('Contact Forms') . '</h5>'];

      $bundles = \Drupal::entityManager()->getBundleInfo('contact_message');
      $formController = \Drupal::entityManager()->getFormObject('contact_message', 'default');

      foreach ($bundles as $bundle_key => $bundle) {
        $stub = entity_create('contact_message', ['contact_form' => $bundle_key]);
        $formController->setEntity($stub);
        $form_id = $formController->getFormId();

        $form['form_settings'][$form_id] = [
          '#type' => 'checkbox',
          '#title' => SafeMarkup::checkPlain($bundle['label']),
          '#default_value' => $this->getFormSettingsValue($form_settings, $form_id),
        ];
      }
    }

    // Node types for node forms.
    if (\Drupal::moduleHandler()->moduleExists('node')) {
      $types = NodeType::loadMultiple();
      if (!empty($types)) {
        // Node forms.
        $form['form_settings']['node_forms'] = ['#markup' => '<h5>' . t('Node Forms') . '</h5>'];
        foreach ($types as $type) {
          $id = $type->getEntityTypeId() . '_node_form';
          $form['form_settings'][$id] = [
            '#type' => 'checkbox',
            '#title' => t('@name node form', ['@name' => $type->label()]),
            '#default_value' => $this->getFormSettingsValue($form_settings, $id),
          ];
        }
      }
    }

    // Comment types for comment forms.
    if (\Drupal::moduleHandler()->moduleExists('comment')) {
      $types = CommentType::loadMultiple();
      if (!empty($types)) {
        $form['form_settings']['comment_forms'] = ['#markup' => '<h5>' . t('Comment Forms') . '</h5>'];
        foreach ($types as $type) {
          $id = 'comment_' . $type->id() . '_form';
          $form['form_settings'][$id] = [
            '#type' => 'checkbox',
            '#title' => t('@name comment form', ['@name' => $type->label()]),
            '#default_value' => $this->getFormSettingsValue($form_settings, $id),
          ];
        }
      }
    }

    // Store the keys we want to save in configuration when form is submitted.
    $keys_to_save = array_keys($form['configuration']);
    foreach ($keys_to_save as $key => $key_to_save) {
      if (strpos($key_to_save, '#') !== FALSE) {
        unset($keys_to_save[$key]);
      }
    }
    $form_state->setStorage(['keys' => $keys_to_save]);

    // For now, manually add submit button. Hopefully, by the time D8 is
    // released, there will be something like system_settings_form() in D7.
    $form['actions']['#type'] = 'container';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Make sure the time limit is a positive integer or 0.
    $time_limit = $form_state->getValue('time_limit');
    if ((is_numeric($time_limit) && $time_limit > 0) || $time_limit === '0') {
      if (ctype_digit($time_limit)) {
        // Good to go.
      }
      else {
        $form_state->setErrorByName('time_limit', t("The time limit must be a positive integer or 0."));
      }
    }
    else {
      $form_state->setErrorByName('time_limit', t("The time limit must be a positive integer or 0."));
    }

    // Make sure Honeypot element name only contains A-Z, 0-9.
    if (!preg_match("/^[-_a-zA-Z0-9]+$/", $form_state->getValue('element_name'))) {
      $form_state->setErrorByName('element_name', t("The element name cannot contain spaces or other special characters."));
    }

    // Make sure Honeypot element name starts with a letter.
    if (!preg_match("/^[a-zA-Z].+$/", $form_state->getValue('element_name'))) {
      $form_state->setErrorByName('element_name', t("The element name must start with a letter."));
    }

    // Make sure Honeypot element name isn't one of the reserved names.
    $reserved_element_names = [
      'name',
      'pass',
      'website',
    ];
    if (in_array($form_state->getValue('element_name'), $reserved_element_names)) {
      $form_state->setErrorByName('element_name', t("The element name cannot match one of the common Drupal form field names (e.g. @names).", ['@names' => implode(', ', $reserved_element_names)]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('honeypot.settings');
    $storage = $form_state->getStorage();

    // Save all the Honeypot configuration items from $form_state.
    foreach ($form_state->getValues() as $key => $value) {
      if (in_array($key, $storage['keys'])) {
        $config->set($key, $value);
      }
    }

    // Save the honeypot forms from $form_state into a 'form_settings' array.
    $config->set('form_settings', $form_state->getValue('form_settings'));

    $config->save();

    // Clear the honeypot protected forms cache.
    \Drupal::cache()->delete('honeypot_protected_forms');

    // Tell the user the settings have been saved.
    drupal_set_message(t('The configuration options have been saved.'));
  }

}
