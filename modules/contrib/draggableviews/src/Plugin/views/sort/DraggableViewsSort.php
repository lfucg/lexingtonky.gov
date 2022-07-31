<?php

namespace Drupal\draggableviews\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Drupal\views\Entity\View;

/**
 * Basic sort handler for Draggableviews Weight.
 *
 * @ViewsSort("draggable_views_sort_default")
 */
class DraggableViewsSort extends SortPluginBase {

  /**
   * The relationship alias.
   *
   * @var string
   */
  public $alias;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['draggable_views_reference'] = ['default' => ''];
    $options['draggable_views_null_order'] = ['default' => 'after'];
    $options['draggable_views_pass_arguments'] = ['default' => 0];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $view_options = $this->getViewSortDataOptions();

    $form['draggable_views_reference'] = [
      '#type' => 'select',
      '#title' => $this->t('Draggable Views Data'),
      '#options' => $view_options,
      '#description' => $this->t('Draggable Views Data to sort on.'),
      '#default_value' => $this->options['draggable_views_reference'],
      '#weight' => -1,
    ];

    $form['draggable_views_null_order'] = [
      '#type' => 'radios',
      '#title' => $this->t('NULL Value Order'),
      '#options' => [
        'before' => "Before",
        'after' => "After",
      ],
      '#description' => $this->t('If an entity does not currently belong in the list, should it be added to the list at the begining or the end.'),
      '#default_value' => $this->options['draggable_views_null_order'],
      '#weight' => -1,
    ];

    $form['draggable_views_pass_arguments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Pass Contextual Filters'),
      '#description' => $this->t('Pass contextual filters from this display to the sort display.'),
      '#default_value' => $this->options['draggable_views_pass_arguments'],
      '#weight' => -1,
    ];
  }

  /**
   * Called to add the sort to a query.
   */
  public function query() {

    // We should variablise these somehow??
    $base = 'draggableviews_structure';
    $base_field = "entity_id";

    // Grab our view/plugin reference.
    list($view_id, $view_display_id) = $this->splitViewSortDataOptions($this->options['draggable_views_reference']);

    $def = $this->definition;
    $def['table'] = $base;
    $def['field'] = $base_field;
    $def['left_table'] = $this->query->view->storage->get('base_table');
    $def['left_field'] = $this->query->view->storage->get('base_field');
    $def['adjusted'] = TRUE;

    $def['extra'][] = [
      'field' => 'view_name',
      'value' => $view_id,
    ];
    $def['extra'][] = [
      'field' => 'view_display',
      'value' => $view_display_id,
    ];

    if (!empty($this->definition['extra'])) {
      $def['extra'] = $this->definition['extra'];
    }

    if (!empty($def['join_id'])) {
      $id = $def['join_id'];
    }
    else {
      $id = 'draggableviews_with_args';
    }
    $join = Views::pluginManager('join')->createInstance($id, $def);

    // Use a short alias for this:
    $alias = $def['table'];

    $this->alias = $this->query->addRelationship($alias, $join, $this->query->view->storage->get('base_table'), $this->relationship);

    if ($this->options['draggable_views_null_order'] == "before") {
      $formula = "!ISNULL($this->alias.$this->realField)";
    }
    else {
      $formula = "ISNULL($this->alias.$this->realField)";
    }

    // We add both to handle ordering of NULL values.
    $this->query->addOrderBy(NULL, $formula, $this->options['order'], $this->alias . "_" . $this->realField);
    $this->query->addOrderBy($this->alias, $this->realField, $this->options['order']);
  }

  /**
   * Grab available draggable views.
   */
  protected function getViewSortDataOptions() {
    $view_data = [];
    $query = \Drupal::entityQuery('view');
    $entity_ids = $query->execute();

    foreach ($entity_ids as $view_id) {
      $v = View::load($view_id);

      $default_display = NULL;
      foreach ($v->get('display') as $display_id => $display) {
        if ($display_id == "default") {
          $default_display = $display;
        }
        else {
          // Use default if fields are not overwritten.
          $fields = !empty($display['display_options']['fields'])
            ? $display['display_options']['fields']
            : $default_display['display_options']['fields'];
          // Need to check that "fields" is an array, view may be configured to
          // render rows otherwise.
          if (is_array($fields) && in_array("draggableviews", array_keys($fields))) {
            if (!isset($view_data[$view_id])) {
              $view_data[$view_id] = [
                'id' => $view_id,
                'label' => $v->label(),
                'displays' => [],
              ];
            }
            $view_data[$view_id]['displays'][$display_id] = [
              'id' => $display_id,
              'label' => $display['display_title'],
            ];
          }
        }
      }
    }

    $view_select = [
      'this' => "This View/Display",
    ];
    foreach ($view_data as $view_id => $v_data) {
      $view_key = $v_data['label'] . " (" . $view_id . ")";
      $view_select[$view_key] = [];

      foreach ($v_data['displays'] as $display) {
        $display_key = $view_id . ":" . $display['id'];

        $view_select[$view_key][$display_key] = $display['label'];
      }
    }

    return $view_select;
  }

  /**
   * Split data into view/display ids.
   */
  protected function splitViewSortDataOptions($data) {
    if (empty($data) || $data == "this") {
      return [$this->view->id(), $this->view->current_display];
    }

    $explode = explode(":", $data);
    if (count($explode) != 2) {
      return ["", ""];
    }
    return $explode;
  }

}
