<?php

namespace Drupal\ckeditor_entity_link\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CKEditorEntityLinkConfigForm.
 *
 * @package Drupal\ckeditor_entity_link\Form
 */
class CKEditorEntityLinkConfigForm extends ConfigFormBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The bundle information service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The entity bundle information service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_info) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->bundleInfo = $bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ckeditor_entity_link.settings',
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
    $entity_types = $this->entityTypeManager->getDefinitions();
    $options = [];
    foreach ($entity_types as $entity_type) {
      if ($entity_type->getGroup() == 'content') {
        $options[$entity_type->id()] = $entity_type->getLabel();
      }
    }
    if (!$options) {
      return ['#markup' => 'No entity types'];
    }
    $form['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types'),
      '#options' => $options,
      '#default_value' => $config->get('entity_types'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateTypeSettings',
        'effect' => 'fade',
      ],
    ];

    $form['bundles'] = [
      '#type' => 'container',
      '#prefix' => '<div id="bundles-wrapper">',
      '#suffix' => '</div>',
    ];

    $selected_types = empty($form_state->getValue('entity_types')) ? $config->get('entity_types') : $form_state->getValue('entity_types');
    foreach ($selected_types as $type) {
      if (!empty($type)) {
        $bundle_info = $this->bundleInfo->getBundleInfo($type);
        $bundles = [];
        foreach ($bundle_info as $bundle => $info) {
          $bundles[$bundle] = $info['label'];
        }
        $form['bundles'][$type] = [
          '#type' => 'fieldset',
          '#title' => $this->t('@bundles', ['@bundles' => $options[$type] . ' bundles']),
        ];
        $form['bundles'][$type][$type . '_bundles'] = [
          '#type' => 'checkboxes',
          '#options' => $bundles,
          '#default_value' => $config->get($type . '_bundles'),
          '#description' => $this->t('Select bundles to be available as autocomplete suggestions. If no selected, all will be available.'),
        ];
      }
    }

    return parent::buildForm($form, $form_state);
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
   * @return \Drupal\Core\Ajax\AjaxResponse
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
