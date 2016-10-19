<?php

/**
 * @file
 * Contains Drupal\fieldblock\Form\FieldBlockConfigForm.
 */

namespace Drupal\fieldblock\Form;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\fieldblock\Controller\FieldBlockController;
use Drupal\fieldblock\BlockEntityStorage;

/**
 * Configuration for select Entity types and delete blocks of unused types.
 */
class FieldBlockConfigForm extends ConfigFormBase {

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\fieldblock\BlockEntityStorage
   */
  protected $storage;

  /**
   * @var \Drupal\fieldblock\Controller\FieldBlockController;
   */
  protected $fieldblock_controller;

  /**
   * Constructs a \Drupal\fieldblock\Form\FieldBlockConfigForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\fieldblock\BlockEntityStorage $storage
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, BlockEntityStorage $storage) {
    parent::__construct($config_factory);
    $this->entityManager = $entity_manager;
    $this->storage = $storage;
    $this->fieldblock_controller = new FieldBlockController();
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity.manager'),
      $container->get('fieldblock.block_storage')
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fieldblock.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_block_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $enabled = $this->fieldblock_controller->getEnabledEntityTypes();
    $form['enabled_entity_types'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable Entity Types'),
      '#options' => $this->getEntityTypeLabels(),
      '#description' => $this->t('Select the Entity Types to expose as field blocks.'),
      '#default_value' => $enabled,
    );
    $orphaned_types = $this->getOrphanedEntityTypes($enabled);
    $cleanup_options = [];
    $entity_type_definitions = $this->entityManager->getDefinitions();
    foreach ($orphaned_types as $entity_type) {
      if (isset($entity_type_definitions[$entity_type]) && $entity_type_definitions[$entity_type] instanceof ContentEntityTypeInterface) {
        // This entity type still exists on the site.
        $cleanup_options[$entity_type] = $entity_type_definitions[$entity_type]->getLabel();
      }
      else {
        // This entity type no longer exists on the site.
        $cleanup_options[$entity_type] = $this->t('Missing entity type') . ': ' . $entity_type;
      }
    }


    if (!empty($cleanup_options)) {
      $form['cleanup'] = [
        '#type' => 'checkboxes',
        '#required' => FALSE,
        '#title' => t('Clean up remaining field blocks of removed entity types'),
        '#description' => t('These entity types no longer exist, but one or more of their field blocks still do. Select the entity type(s) of which the field block(s) must be removed.'),
        '#default_value' => [],
        '#options' => $cleanup_options,
      ];
    }

    return parent::buildForm($form, $form_state);
  }


  protected function getAllEntityTypes() {
    return array_keys($this->entityManager->getDefinitions());
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

    $clear_cache = FALSE;
    $previous_entity_types = $this->fieldblock_controller->getEnabledEntityTypes();
    $new_entity_types = $form_state->getValue('enabled_entity_types');

    if ($previous_entity_types != $new_entity_types) {
      $clear_cache = TRUE;
    }

    // Remove all blocks for entity types that are no longer enabled.
    if ($cleanup = $form_state->getValue('cleanup')) {
      foreach ($cleanup as $entity_type => $value) {
        if ($value !== 0) {
          // Find and delete the remaining blocks for this entity type.
          $this->storage->deleteBlocksForEntityType($entity_type);
          $clear_cache = TRUE;
          drupal_set_message($this->t('Remaining field blocks for the %type entity have been deleted.', ['%type' => $entity_type]));
        }
        else {
          if (in_array($entity_type, $this->getAllEntityTypes())) {
            // Keep the entity type in the settings if it still exists.
            $new_entity_types[$entity_type] = $entity_type;
          }
        }
      }
    }

    $this->config('fieldblock.settings')
      ->set('enabled_entity_types', $new_entity_types)
      ->save();

    if ($clear_cache) {
      // Invalidate the block cache to update fieldblock derivatives.
      if (\Drupal::moduleHandler()->moduleExists('block')) {
        \Drupal::service('plugin.manager.block')->clearCachedDefinitions();
      }
    }
  }

  /**
   * Get Entity Type labels for all compatible Entity Types.
   *
   * @return array
   */
  protected function getEntityTypeLabels() {
    $definitions = $this->entityManager->getDefinitions();
    $labels = [];
    /** @var \Drupal\Core\Entity\EntityTypeInterface $definition */
    foreach ($definitions as $definition) {
      if ($this->fieldblock_controller->isFieldBlockCompatible($definition)) {
        $labels[$definition->id()] = $definition->getLabel();
      }
    }
    return $labels;
  }

  /**
   * Get all entity types that have Field Blocks but are either:
   *  1. No longer set to be used with this module
   *  2. Don't exist on the site.
   *
   * @param array $enabled_entity_types
   *   Currently enabled entity types.
   * @return array Entity type ids.
   *   Entity type ids.
   * @todo param and return doc blocks must specify array of what, eg. string[].
   */
  protected function getOrphanedEntityTypes($enabled_entity_types) {
    $orphaned_types = [];
    $entity_types_used = $this->storage->getEntityTypesUsed();
    $all_entity_types = $this->getAllEntityTypes();
    foreach ($entity_types_used as $used_type) {
      if (!in_array($used_type, $all_entity_types) || !in_array($used_type, $enabled_entity_types)) {
        $orphaned_types[] = $used_type;
      }
    }

    return $orphaned_types;
  }

}
