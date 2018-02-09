<?php

/**
 * @file
 * Contains Drupal\taxonomy_menu\Form\TaxonomyMenuForm.
 */

namespace Drupal\taxonomy_menu\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;

/**
 * Class TaxonomyMenuForm.
 *
 * @package Drupal\taxonomy_menu\Form
 */
class TaxonomyMenuForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var $taxonomy_menu \Drupal\taxonomy_menu\Entity\TaxonomyMenu */
    $taxonomy_menu = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $taxonomy_menu->label(),
      '#description' => $this->t("Label for the Taxonomy Menu."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $taxonomy_menu->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\taxonomy_menu\Entity\TaxonomyMenu::load',
      ),
      '#disabled' => !$taxonomy_menu->isNew(),
    );

    // Vocabulary selection.
    $options = [];
    $vocabulary_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary');
    foreach ($vocabulary_storage->loadMultiple() as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    $form['vocabulary'] = [
      '#type' => 'select',
      '#title' => $this->t('Vocabulary'),
      '#options' => $options,
      '#default_value' => $taxonomy_menu->getVocabulary(),
    ];

    // Menu selection.
    $options = [];
    $menu_storage = \Drupal::entityTypeManager()->getStorage('menu');
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
      '#options' => range(1,9),
    ];

    // Menu selection.
    $custom_menus = Menu::loadMultiple();
    foreach ($custom_menus as $menu_name => $menu) {
      $custom_menus[$menu_name] = $menu->label();
    }
    asort($custom_menus);

    $menu_parent_selector = \Drupal::service('menu.parent_form_selector');
    $available_menus = $custom_menus;
    $menu_options = $menu_parent_selector->getParentSelectOptions(null, $available_menus);

    $form['menu_parent'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent menu link'),
      '#options' => $menu_options,
      '#default_value' => $taxonomy_menu->getMenuParent(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $taxonomy_menu = $this->entity;
    $status = $taxonomy_menu->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label Taxonomy Menu.', array(
        '%label' => $taxonomy_menu->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label Taxonomy Menu was not saved.', array(
        '%label' => $taxonomy_menu->label(),
      )));
    }
    $form_state->setRedirectUrl($taxonomy_menu->toUrl('collection'));
  }

}
