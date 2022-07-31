<?php

/**
 * @file
 * Contains \Drupal\paragraphs_jquery_ui_accordion\Plugin\Field\FieldFormatter\ParagraphsJQueryUIAccordionFormatter.
 */

namespace Drupal\paragraphs_jquery_ui_accordion\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Component\Transliteration\TransliterationInterface;

/**
 * Plugin implementation of the 'paragraphs_jquery_ui_accordion_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "paragraphs_jquery_ui_accordion_formatter",
 *   label = @Translation("Paragraphs jQuery UI Accordion"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParagraphsJQueryUIAccordionFormatter extends EntityReferenceRevisionsFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type id.
   *
   * @var string
   */
  protected $entityTypeId = '';

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * ParagraphsJQueryUIAccordionFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeBundleInfoInterface $bundle_info, EntityFieldManagerInterface $entity_field_manager, LoggerChannelFactoryInterface $logger_factory, EntityDisplayRepositoryInterface $entity_display_repository, TransliterationInterface $transliteration) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->bundleInfo = $bundle_info;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeId = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $this->loggerFactory = $logger_factory;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->transliteration = $transliteration;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager'),
      $container->get('logger.factory'),
      $container->get('entity_display.repository'),
      $container->get('transliteration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'bundle' => '',
      'title' => '',
      'content' => '',
      'active' => 1,
      'autoscroll' => FALSE,
      'autoscroll_offset' => '',
      'autoscroll_offset_toolbar' => FALSE,
      'view_mode' => 'default',
      'simple_id' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $bundles = $this->getBundles();
    $bundle_fields = $this->getBundleFields();

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Paragraph bundle'),
      '#default_value' => $this->getSetting('bundle'),
      '#options' => $bundles,
    ];
    $form['title'] = [
      '#type' => 'select',
      '#title' => $this->t('Paragraph title'),
      '#default_value' => $this->getSetting('title'),
      '#options' => $bundle_fields,
    ];
    $form['content'] = [
      '#type' => 'select',
      '#title' => $this->t('Paragraph content'),
      '#default_value' => $this->getSetting('content'),
      '#options' => $bundle_fields,
    ];
    $form['view_mode'] = array(
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type')),
      '#title' => $this->t('View mode'),
      '#description' => $this->t('This view mode will be applied for content field selected above.'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    );
    $form['active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#description' => $this->t('Makes first panel is open.'),
      '#default_value' => $this->getSetting('active'),
    ];
    $form['simple_id'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Simple Ids'),
      '#description' => $this->t('This makes each accordion id in numerical order (#1, #2, #3 etc).<br />Note this may break functionality if you are using multiple accordions on the same page.'),
      '#default_value' => $this->getSetting('simple_id'),
    ];
    $form['autoscroll'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('AutoScroll'),
      '#description' => $this->t('Scrolls to active accordion item.'),
      '#default_value' => $this->getSetting('autoscroll'),
      '#attributes' => [
        'setting-name' => 'autoscroll'
      ]
    ];
    $form['autoscroll_offset'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AutoScroll offset'),
      '#description' => $this->t('Adds extra margin if such exist in your page layout (for example if enabled admin_toolbar module, then you need set 80px).<br />Leave empty if not needed.'),
      '#default_value' => $this->getSetting('autoscroll_offset'),
      '#states' => [
        'visible' => [
          [':input[setting-name="autoscroll"]' => ['checked' => TRUE]],
        ]
      ],
      '#size' => 20,
      '#field_suffix' => 'px'
    ];
    $form['autoscroll_offset_toolbar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply offset only for admin toolbar.'),
      '#default_value' => $this->getSetting('autoscroll_offset_toolbar'),
      '#states' => [
        'visible' => [
          [':input[setting-name="autoscroll"]' => ['checked' => TRUE]],
        ]
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $settings = $this->getSettings();
    $view_modes = $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));

    $summary[] = t('Paragraph bundle: %bundle', ['%bundle' => $settings['bundle']]);
    $summary[] = t('Paragraph title: %title', ['%title' => $settings['title']]);
    $summary[] = t('Paragraph content: %content', ['%content' => $settings['content']]);
    $summary[] = t('Paragraph content view mode: %view_mode', ['%view_mode' => isset($view_modes[$settings['view_mode']]) ? $view_modes[$settings['view_mode']] : $settings['view_mode']]);
    $summary[] = t('AutoScroll: %autoscroll', ['%autoscroll' => $settings['autoscroll'] ? t('enabled') : t('disabled')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getSettings();
    $elements = [];

    $moduleHandler = \Drupal::service('module_handler');
    $quickedit = FALSE;
    if ($moduleHandler->moduleExists('quickedit')){
      $quickedit = TRUE;
    }

    $accordion_id = $this->getAccordionId($items->getEntity()->id());

    $js_options = [
      'ids' => [$items->getEntity()->id() => $accordion_id],
      'active' => $this->getSetting('active') ? 1 : 0,
      'autoscroll' => $this->getSetting('autoscroll'),
      'autoscroll_offset' => $this->getSetting('autoscroll_offset'),
    ];

    if ($settings['autoscroll_offset_toolbar'] && $moduleHandler->moduleExists('toolbar')) {
      if (!\Drupal::currentUser()->hasPermission('access toolbar')) {
        unset($js_options['autoscroll_offset']);
      }
    }

    $elements[0]['accordion'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $accordion_id],
      '#attached' => [
        'library' => 'paragraphs_jquery_ui_accordion/accordion',
        'drupalSettings' => [
          'paragraphs_jquery_ui_accordion' => $js_options,
        ],
      ],
    ];

    $title_attributes = ['class' => ['accordion-title']];
    $content_attributes = ['class' => ['accordion-description']];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', array('@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()));
        return $elements;
      }

      $title = $entity->get($this->getSetting('title'))->value;
      $content = $entity->get($this->getSetting('content'))->view($settings['view_mode']);

      if (!$settings['simple_id']) {
        $id = Html::getUniqueId($this->transliteration->transliterate($title));
      }
      else {
        $id = $delta + 1;
      }

      // This variable is needed to avoid js errors during render of paragraph's fields.
      if ($quickedit) {
        $content_attributes['data-quickedit-entity-id'] = $entity->getEntityTypeId() . '/' . $entity->id();
      }

      $elements[0]['accordion'][$delta] = [
        '#theme' => 'paragraphs_jquery_ui_accordion_formatter',
        '#title' => $title,
        '#content' => $content,
        '#id' => $id,
        '#title_attributes' => $title_attributes,
        '#content_attributes' => $content_attributes,
      ];

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += ['resource' => $entity->toUrl()->toString()];
      }
      $depth = 0;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();
    return $storage->isMultiple() && $storage->getSetting('target_type') === 'paragraph';
  }

  /**
   * Gets a bundles array suitable for form options.
   *
   * @return array
   *   The bundles array that can be passed to form element of type select.
   */
  protected function getBundles() {
    foreach ($this->bundleInfo->getBundleInfo($this->entityTypeId) as $key => $bundle) {
      $bundles[$key] = $bundle['label'];
    }
    return isset($bundles) ? $bundles : [];
  }

  /**
   * Gets a bundle fields array suitable for form options.
   *
   * @return array
   *   The fields array that can be passed to form element of type select.
   */
  protected function getBundleFields() {
    foreach ($this->getBundles() as $bundle_name => $bundle) {
      $field_definitions = $this->entityFieldManager->getFieldDefinitions($this->entityTypeId, $bundle_name);
      foreach ($field_definitions as $field_name => $field_definition) {
        if (!$field_definition->getFieldStorageDefinition()->isBaseField()) {
          $bundle_fields[$field_name] = $field_definition->getLabel();
        }
      }
    }
    return isset($bundle_fields) ? $bundle_fields : [];
  }

  /**
   * Generates unique accordion identifier for html attribute.
   *
   * @param int $id
   *   Unique identifier.
   *
   * @return string
   *   Returns unique accordion id.
   */
  protected function getAccordionId($id) {
    return 'accordion-' . $id;
  }

}
