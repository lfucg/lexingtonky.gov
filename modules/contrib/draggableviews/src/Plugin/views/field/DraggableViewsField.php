<?php

namespace Drupal\draggableviews\Plugin\views\field;

use Drupal\Core\Render\Markup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\draggableviews\DraggableViews;
use Drupal\views\Plugin\views\field\BulkForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a draggableviews form element.
 *
 * @ViewsField("draggable_views_field")
 */
class DraggableViewsField extends BulkForm {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The action storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $actionStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;
  /**
   * The Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Sets the current_user service.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   *
   * @return $this
   */
  public function setCurrentUser(AccountInterface $current_user) {
    $this->currentUser = $current_user;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $datasource */
    $bulk_form = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $bulk_form->setCurrentUser($container->get('current_user'));
    return $bulk_form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['draggableview_help'] = [
      '#markup' => $this->t("A draggable element will be added to the first table column. You do not have to set this field as the first column in your View."),
    ];
    parent::buildOptionsForm($form, $form_state);
    // Remove all the fields that would break this or are completely ignored
    // when rendering the drag interface.
    unset($form['custom_label']);
    unset($form['label']);
    unset($form['element_label_colon']);
    unset($form['action_title']);
    unset($form['include_exclude']);
    unset($form['selected_actions']);
    unset($form['exclude']);
    unset($form['alter']);
    unset($form['empty_field_behavior']);
    unset($form['empty']);
    unset($form['empty_zero']);
    unset($form['hide_empty']);
    unset($form['hide_alter_empty']);
  }

  /**
   * {@inheritdoc}
   */
  // @codingStandardsIgnoreStart
  public function render_item($count, $item) {
    // @codingStandardsIgnoreEnd
    // Using internal method. @todo Reckeck after drupal stable release.
    return Markup::create('<!--form-item-' . $this->options['id'] . '--' . $this->view->row_index . '-->');
  }

  /**
   * {@inheritdoc}
   */
  public function viewsForm(&$form, FormStateInterface $form_state) {
    $form[$this->options['id']] = [
      '#tree' => TRUE,
    ];

    $draggableviews = new DraggableViews($this->view);

    foreach ($this->view->result as $row_index => $row) {
      $form[$this->options['id']][$row_index] = [
        '#tree' => TRUE,
      ];

      // Add weight.
      $form[$this->options['id']][$row_index]['weight'] = [
        '#type' => 'textfield',
        '#size' => '5',
        '#maxlength' => '5',
        '#value' => $row->draggableviews_structure_weight,
        '#attributes' => ['class' => ['draggableviews-weight']],
      ];

      // Item to keep id of the entity.
      $form[$this->options['id']][$row_index]['id'] = [
        '#type' => 'hidden',
        '#value' => $this->getEntity($row)->id(),
        '#attributes' => ['class' => ['draggableviews-id']],
      ];

      // Add parent.
      $form[$this->options['id']][$row_index]['parent'] = [
        '#type' => 'hidden',
        '#default_value' => $draggableviews->getParent($row_index),
        '#attributes' => ['class' => ['draggableviews-parent']],
      ];
    }

    if ($this->currentUser->hasPermission('access draggableviews')) {
      $options = [
        'table_id' => $draggableviews->getHtmlId(),
        'action' => 'match',
        'relationship' => 'group',
        'group' => 'draggableviews-parent',
        'subgroup' => 'draggableviews-parent',
        'source' => 'draggableviews-id',
      ];
      drupal_attach_tabledrag($form, $options);
    }
  }

}
