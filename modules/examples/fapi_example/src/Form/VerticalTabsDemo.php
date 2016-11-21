<?php

namespace Drupal\fapi_example\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the vertical tabs demo form controller.
 *
 * This example demonstrates the use of \Drupal\Core\Render\Element\VerticalTabs
 * to group input elements according category.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class VerticalTabsDemo extends DemoBase {

  /**
   * Build the form.
   *
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['information'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-publication',
    ];

    $form['author'] = [
      '#type' => 'details',
      '#title' => 'Author',
      '#group' => 'information',
    ];

    $form['author']['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
    ];

    $form['publication'] = [
      '#type' => 'details',
      '#title' => t('Publication'),
      '#group' => 'information',
    ];

    $form['publication']['publisher'] = [
      '#type' => 'textfield',
      '#title' => t('Publisher'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * @inheritdoc
   */
  public function getFormId() {
    return 'fapi_example_vertical_tabs_demo';
  }

}
