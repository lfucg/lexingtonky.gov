<?php

namespace Drupal\fieldblock\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a fieldblock.
 *
 * @Block(
 *   id = "fieldblock",
 *   admin_label = @Translation("Field as Block"),
 *   deriver = "Drupal\fieldblock\Plugin\Derivative\FieldBlockDeriver"
 * )
 */
class FieldBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field formatter plugin manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterPluginManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The language manager
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The entity to be used when displaying the block.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $fieldBlockEntity;

  /**
   * Constructs a FieldBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_plugin_manager
   *   The field formatter plugin manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager, FormatterPluginManager $formatter_plugin_manager, RouteMatchInterface $route_match, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->formatterPluginManager = $formatter_plugin_manager;
    $this->routeMatch = $route_match;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.field.formatter'),
      $container->get('current_route_match'),
      $container->get('language_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_from_field' => TRUE,
      'field_name' => '',
      'formatter_id' => '',
      'formatter_settings' => [],
    ];
  }

  /**
   * Returns field options.
   *
   * @return array
   *   Array of field option names keyed by their machine name.
   */
  protected function getFieldOptions() {
    $field_definitions = $this->entityFieldManager->getFieldStorageDefinitions($this->getDerivativeId());
    $options = [];
    foreach ($field_definitions as $definition) {
      $options[$definition->getName()] = $definition->getLabel();
    }
    return $options;
  }

  /**
   * Returns field formatter names.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field.
   *
   * @return array
   *   Array of formatter names keyed by field type.
   */
  protected function getFormatterOptions(FieldDefinitionInterface $field_definition) {
    $options = $this->formatterPluginManager->getOptions($field_definition->getType());
    foreach ($options as $id => $label) {
      $definition = $this->formatterPluginManager->getDefinition($id, FALSE);
      $formatter_plugin_class = isset($definition['class']) ? $definition['class'] : NULL;
      $applicable = $formatter_plugin_class instanceof FormatterInterface && $formatter_plugin_class::isApplicable($field_definition);
      if ($applicable) {
        unset($options[$id]);
      }
    }
    return $options;
  }

  /**
   * Gets the field definition.
   *
   * A FieldBlock works on an entity type across bundles, and thus only has
   * access to field storage definitions. In order to be able to use formatters,
   * we create a generic field definition out of that storage definition.
   *
   * @param string $field_name
   *   The field name.
   *
   * @see BaseFieldDefinition::createFromFieldStorageDefinition()
   * @see \Drupal\views\Plugin\views\field\Field::getFieldDefinition()
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   The field definition used by this block.
   */
  protected function getFieldDefinition($field_name) {
    $field_storage_config = $this->getFieldStorageDefinition($field_name);
    return BaseFieldDefinition::createFromFieldStorageDefinition($field_storage_config);
  }

  /**
   * Gets the field storage definition.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface
   *   The field storage definition used by this block.
   */
  protected function getFieldStorageDefinition($field_name) {
    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($this->getDerivativeId());
    return $field_storage_definitions[$field_name];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // This method receives a sub form state instead of the full form state.
    // There is an ongoing discussion around this which could result in the
    // passed form state going back to a full form state. In order to prevent
    // future breakage because of a core update we'll just check which type of
    // FormStateInterface we've been passed and act accordingly.
    // @See https://www.drupal.org/node/2798261
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }

    $form['label_from_field'] = [
      '#title' => $this->t('Use field label as block title'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['label_from_field'],
    ];

    $form['field_name'] = [
      '#title' => $this->t('Field'),
      '#type' => 'select',
      '#options' => $this->getFieldOptions(),
      '#default_value' => $this->configuration['field_name'],
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'blockFormChangeFieldOrFormatterAjax'],
        'wrapper' => 'edit-block-formatter-wrapper',
      ],
    ];

    $form['formatter'] = [
      '#type' => 'container',
      '#id' => 'edit-block-formatter-wrapper',
    ];

    $field_name = $form_state->getValue(['settings', 'field_name'], $this->configuration['field_name']);
    $field_definition = NULL;
    $formatter_id = $form_state->getValue(['settings', 'formatter', 'id'], $this->configuration['formatter_id']);

    if ($field_name) {
      $field_definition = $this->getFieldDefinition($field_name);
      $formatter_options = $this->getFormatterOptions($field_definition);
      if (empty($formatter_options)) {
        $formatter_id = '';
      }
      else {
        if (empty($formatter_id)) {
          $formatter_id = key($formatter_options);
        }
        $form['formatter']['id'] = [
          '#title' => $this->t('Formatter'),
          '#type' => 'select',
          '#options' => $formatter_options,
          '#default_value' => $this->configuration['formatter_id'],
          '#required' => TRUE,
          '#ajax' => [
            'callback' => [$this, 'blockFormChangeFieldOrFormatterAjax'],
            'wrapper' => 'edit-block-formatter-wrapper',
          ],
        ];
      }
    }

    $form['formatter']['change'] = [
      '#type' => 'submit',
      '#name' => 'fieldblock_change_field',
      '#value' => $this->t('Change field'),
      '#attributes' => ['class' => ['js-hide']],
      '#limit_validation_errors' => [['settings']],
      '#submit' => [[get_class($this), 'blockFormChangeFieldOrFormatter']],
    ];

    if ($formatter_id) {
      $formatter_settings = $this->configuration['formatter_settings'] + $this->formatterPluginManager->getDefaultSettings($formatter_id);
      $formatter_options = [
        'field_definition' => $field_definition,
        'view_mode' => '_custom',
        'configuration' => [
          'type' => $formatter_id,
          'settings' => $formatter_settings,
          'label' => '',
          'weight' => 0,
        ],
      ];

      if ($formatter_plugin = $this->formatterPluginManager->getInstance($formatter_options)) {
        $formatter_settings_form = $formatter_plugin->settingsForm($form, $form_state);
        // Convert field UI selector states to work in the block configuration
        // form.
        FormHelper::rewriteStatesSelector($formatter_settings_form,
          "fields[{$field_name}][settings_edit_form]",
          'settings[formatter][settings]');
      }
      if (!empty($formatter_settings_form)) {
        $form['formatter']['settings'] = $formatter_settings_form;
        $form['formatter']['settings']['#type'] = 'fieldset';
        $form['formatter']['settings']['#title'] = $this->t('Formatter settings');
      }
    }

    return $form;
  }

  /**
   * Element submit handler for non-JS field/formatter changes.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function blockFormChangeFieldOrFormatter(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Ajax callback on changing field_name or formatter_id form element.
   *
   * @param array $form
   *   The form.
   *
   * @return array
   *   The part of the form that has changed.
   */
  public function blockFormChangeFieldOrFormatterAjax(array $form) {
    return $form['settings']['formatter'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['label_from_field'] = $form_state->getValue('label_from_field');
    $this->configuration['field_name'] = $form_state->getValue('field_name');
    $this->configuration['formatter_id'] = $form_state->getValue(['formatter', 'id'], '');
    $this->configuration['formatter_settings'] = $form_state->getValue(['formatter', 'settings'], []);
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\views\Plugin\views\field\Field::calculateDependencies()
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    // Add the module providing the configured field storage as a dependency.
    if (($field_storage_definition = $this->getFieldStorageDefinition($this->configuration['field_name'])) && $field_storage_definition instanceof EntityInterface) {
      $dependencies['config'][] = $field_storage_definition->getConfigDependencyName();
    }
    // Add the module providing the formatter.
    if (!empty($this->configuration['formatter_id'])) {
      $dependencies['module'][] = $this->formatterPluginManager->getDefinition($this->configuration['formatter_id'])['provider'];
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $entity = $this->getEntity();

    if ($entity) {
      $field = $entity->get($this->configuration['field_name']);
      return AccessResult::allowedIf(!$field->isEmpty() && $field->access('view', $account));
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $entity = $this->getEntity();

    if ($entity) {
      $build['field'] = $this->getTranslatedFieldFromEntity($entity)->view([
        'label' => 'hidden',
        'type' => $this->configuration['formatter_id'],
        'settings' => $this->configuration['formatter_settings'],
      ]);
      if ($this->configuration['label_from_field'] && !empty($build['field']['#title'])) {
        $build['#title'] = $build['field']['#title'];
      }
    }

    return $build;
  }

  /**
   * Ensure that the field gets correctly translated into the current language
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   */
  private function getTranslatedFieldFromEntity(ContentEntityInterface $entity) {
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $field = $entity->get($this->configuration['field_name']);

    if ($entity->hasTranslation($language)) {
      $translatedEntity = $entity->getTranslation($language);
      $adapter = EntityAdapter::createFromEntity($translatedEntity);
      $field->setContext($this->configuration['field_name'], $adapter);
    }

    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $entity = $this->getEntity();
    if ($entity) {
      return $entity->getCacheTags();
    }
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // This block must be cached per route: every entity has its own canonical
    // url and its own fields.
    return ['route'];
  }

  /**
   * Finds the entity to be used when displaying the block.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity to be used when displaying the block.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getEntity() {
    if (!isset($this->fieldBlockEntity)) {
      $entity_type = $this->getDerivativeId();
      $entity = NULL;
      $field_name = $this->configuration['field_name'];
      $route_name = $this->routeMatch->getRouteName();
      $is_canonical_route = $route_name === 'entity.' . $entity_type . '.canonical';
      $is_latest_route = $route_name == 'entity.' . $entity_type . '.latest_version';

      if ($is_canonical_route || $is_latest_route) {
        $entity = $this->routeMatch->getParameter($entity_type);
      }
      elseif ($entity_type === 'node') {
        if ($route_name == 'entity.node.revision') {
          $entity_revision = $this->routeMatch->getParameter('node_revision');
          $entity = $this->entityTypeManager->getStorage('node')->loadRevision($entity_revision);
        }
        elseif ($route_name == 'entity.node.preview' && $this->routeMatch->getParameter('view_mode_id') === 'full') {
          $entity = $this->routeMatch->getParameter('node_preview');
        }
      }

      if ($entity instanceof ContentEntityInterface && $entity->getEntityTypeId() === $entity_type && $entity->hasField($field_name)) {
        $this->fieldBlockEntity = $entity;
      }
    }
    return $this->fieldBlockEntity;
  }

}
