<?php

namespace Drupal\taxonomy_menu\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;
use Drupal\Core\Menu\MenuParentFormSelector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManager;

/**
 * Builds the taxonomy menu form.
 *
 * @package Drupal\taxonomy_menu\Form
 */
class TaxonomyMenuForm extends EntityForm {

  /**
   * The menu parent form selector.
   *
   * @var \Drupal\Core\Menu\MenuParentFormSelector
   */
  protected $menuParentSelector;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * Constructs a new TaxonomyMenuMenuLink.
   *
   * @param \Drupal\Core\Menu\MenuParentFormSelector $menu_parent_selector
   *   The menu parent selector.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(MenuParentFormSelector $menu_parent_selector, EntityFieldManager $entity_field_manager) {
    $this->menuParentSelector = $menu_parent_selector;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('menu.parent_form_selector'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\taxonomy_menu\Entity\TaxonomyMenu $taxonomy_menu */
    $taxonomy_menu = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $taxonomy_menu->label(),
      '#description' => $this->t("Label for the Taxonomy Menu."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $taxonomy_menu->id(),
      '#machine_name' => [
        'exists' => '\Drupal\taxonomy_menu\Entity\TaxonomyMenu::load',
      ],
      '#disabled' => !$taxonomy_menu->isNew(),
    ];

    // Vocabulary selection.
    $options = [];
    $vocabulary_storage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
    foreach ($vocabulary_storage->loadMultiple() as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    $form['vocabulary'] = [
      '#type' => 'select',
      '#title' => $this->t('Vocabulary'),
      '#options' => $options,
      '#default_value' => $taxonomy_menu->getVocabulary(),
      '#ajax' => [
        'callback' => '::ajaxReplaceDescriptionFieldForm',
        'wrapper' => 'description-field-container',
        'method' => 'replace',
      ],
    ];

    // Description field selection.
    $form['description_container'] = [
      '#type' => 'container',
      '#prefix' => '<div id="description-field-container">',
      '#suffix' => '</div>',
    ];

    $selected_vocabulary = $taxonomy_menu->getVocabulary();

    if ($selected_vocabulary) {
      $field_definitions = $this->entityFieldManager->getFieldDefinitions('taxonomy_term', $selected_vocabulary);

      // Build a field options array.
      $field_options = ['' => $this->t('none')];
      if (count($field_definitions)) {
        foreach ($field_definitions as $field_name => $field_definition) {
          $field_options[$field_name] = $field_definition->getName();
        }
      }

      if (count($field_options)) {
        $form['description_container']['description_field_name'] = [
          '#type' => 'select',
          '#title' => $this->t('Description field'),
          '#description' => $this->t('Select the field to be used for the menu item description.'),
          '#options' => $field_options,
          '#default_value' => $taxonomy_menu->getDescriptionFieldName(),
        ];
      }
    }

    // Menu selection.
    $options = [];
    $menu_storage = $this->entityTypeManager->getStorage('menu');
    foreach ($menu_storage->loadMultiple() as $menu) {
      $options[$menu->id()] = $menu->label();
    }
    $form['menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu'),
      '#options' => $options,
      '#default_value' => $taxonomy_menu->getMenu(),
    ];
    $form['expanded'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('All menus entries expanded'),
      '#default_value' => $taxonomy_menu->expanded,
    ];
    $form['depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Depth'),
      '#default_value' => $taxonomy_menu->getDepth(),
      '#options' => range(1, 9),
    ];

    // Menu selection.
    $custom_menus = Menu::loadMultiple();
    foreach ($custom_menus as $menu_name => $menu) {
      $custom_menus[$menu_name] = $menu->label();
    }
    asort($custom_menus);

    $available_menus = $custom_menus;
    $menu_options = $this->menuParentSelector->getParentSelectOptions(NULL, $available_menus);

    $form['menu_parent'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent menu link'),
      '#options' => $menu_options,
      '#default_value' => $taxonomy_menu->getMenuParent(),
    ];

    // If this checkbox is active, use the term weight for the menu item order.
    // Otherwise the menu items will be sorted alphabetically.
    // The default is order by weight.
    $form['use_term_weight_order'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use term weight order'),
      '#default_value' => isset($taxonomy_menu->use_term_weight_order) ? $taxonomy_menu->use_term_weight_order : TRUE,
    ];

    return $form;
  }

  /**
   * AJAX callback; Builds the description field selector.
   */
  public static function ajaxReplaceDescriptionFieldForm(array &$form, FormStateInterface $form_state) {
    return $form['description_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $taxonomy_menu = $this->entity;
    $status = $taxonomy_menu->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Taxonomy Menu.', [
        '%label' => $taxonomy_menu->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Taxonomy Menu was not saved.', [
        '%label' => $taxonomy_menu->label(),
      ]));
    }
    $form_state->setRedirectUrl($taxonomy_menu->toUrl('collection'));
  }

}
