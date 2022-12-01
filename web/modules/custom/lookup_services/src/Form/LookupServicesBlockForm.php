<?php

namespace Drupal\lookup_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Lookup Services block form.
 */
class LookupServicesBlockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lookup_services_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // We'll likely clean this up a little going forward but this is the idea.
    $form['address'] = [
      '#type' => 'textfield',
      '#placeholder' => 'Start typing your address...',
      '#attributes' => [
        'class' => ['js-lex-address mx-auto'],
      ],
    ];

    $form['#attached']['library'][] = 'lookup_services/global';
    $form['#attributes']['class'][] = 'mx-auto text-center';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.your_address',
        '<div class="my_top_message">' . t('Your address is @result', ['@result' => $form_state->getValue('address')]) . '</div>'
      )
    );

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state){}

}
