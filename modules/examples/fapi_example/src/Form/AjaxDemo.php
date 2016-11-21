<?php

namespace Drupal\fapi_example\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the ajax demo form controller.
 *
 * This example demonstrates using ajax callbacks to populate the options of a
 * color select element dynamically based on the value selected in another
 * select element in the form.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class AjaxDemo extends DemoBase {
  /*
   * Possible colors to choose from.
   * Used by colorCallback to determine which colors to include in the
   * select element.
   */

  private $colors = [
    'warm' => [
      'red' => 'Red',
      'orange' => 'Orange',
      'yellow' => 'Yellow',
    ],
    'cool' => [
      'blue' => 'Blue',
      'purple' => 'Purple',
      'green' => 'Green',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /*
     * The #ajax attribute used in the temperature input element defines an ajax
     * callback that will invoke the colorCallback method on this form object.
     * Whenever the temperature element changes, it will invoke this callback
     * and replace the contents of the color_wrapper container with the results
     * of this method call.
     */
    $form['temperature'] = [
      '#title' => $this->t('Temperature'),
      '#type' => 'select',
      '#options' => ['warm' => 'Warm', 'cool' => 'Cool'],
      '#empty_option' => $this->t('-select'),
      '#ajax' => [
        // Could also use [ $this, 'colorCallback'].
        'callback' => '::colorCallback',
        'wrapper' => 'color-wrapper',
      ],
    ];

    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $form['color_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'color-wrapper'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fapi_example_ajax_demo';
  }

  /**
   * Implements callback for Ajax event on color selection.
   *
   * @param array $form
   *   From render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Color selection section of the form.
   */
  public function colorCallback(array &$form, FormStateInterface $form_state) {
    $temperature = $form_state->getValue('temperature');

    // Add a color element to the color_wrapper container using the value
    // from temperature to determine which colors to include in the select
    // element.
    $form['color_wrapper']['color'] = [
      '#type' => 'select',
      '#title' => $this->t('Color'),
      '#options' => $this->colors[$temperature],
    ];

    return $form['color_wrapper'];
  }

}
