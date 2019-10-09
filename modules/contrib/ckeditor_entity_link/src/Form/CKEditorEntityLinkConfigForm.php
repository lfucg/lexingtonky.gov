<?php

namespace Drupal\ckeditor_entity_link\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;


/**
 * Class CKEditorEntityLinkConfigForm.
 *
 * @package Drupal\ckeditor_entity_link\Form
 */
class CKEditorEntityLinkConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ckeditor_entity_link.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor_entity_link_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ckeditor_entity_link.settings');

    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $options = [];
    foreach ($entity_types as $entity_type) {
      if ($entity_type->getGroup() == 'content') {
        $options[$entity_type->id()] = $entity_type->getLabel();
      }
    }
    if (!$options) {
      return ['#markup' => 'No entity types'];
    }
    $form['entity_types'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Entity types'),
      '#options' => $options,
      '#default_value' => $config->get('entity_types'),
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => '::updateTypeSettings',
        'effect' => 'fade',
      ),
    );

    $form['bundles'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="bundles-wrapper">',
      '#suffix' => '</div>',
    );

    $selected_types = empty($form_state->getValue('entity_types')) ? $config->get('entity_types') : $form_state->getValue('entity_types');
    foreach ($selected_types as $type) {
      if (!empty($type)) {
        $bundle_info = \Drupal::entityManager()->getBundleInfo($type);
        $bundles = array();
        foreach ($bundle_info as $bundle => $info) {
          $bundles[$bundle] = $info['label'];
        }
        $form['bundles'][$type] = array(
          '#type' => 'fieldset',
          '#title' => t($options[$type] . ' bundles'),
        );
        $form['bundles'][$type][$type . '_bundles'] = array(
          '#type' => 'checkboxes',
          '#options' => $bundles,
          '#default_value' => $config->get($type . '_bundles'),
          '#description' => t('Select bundles to be available as autocomplete suggestions. If no selected, all will be available.')
        );
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('ckeditor_entity_link.settings');

    $types = $form_state->getValue('entity_types');

    $config->set('entity_types', $types);
    foreach ($types as $type) {
      $config->set($type . '_bundles', $form_state->getValue($type . '_bundles'));
    }

    $config->save();
  }

  /**
   * Ajax callback to update the form fields which depend on embed type.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return AjaxResponse
   *   Ajax response with updated options for the embed type.
   */
  public function updateTypeSettings(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Update options for entity type bundles.
    $response->addCommand(new ReplaceCommand(
      '#bundles-wrapper',
      $form['bundles']
    ));

    return $response;
  }
}
