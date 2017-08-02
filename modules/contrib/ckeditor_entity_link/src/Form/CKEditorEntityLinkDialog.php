<?php

namespace Drupal\ckeditor_entity_link\Form;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * Provides a link dialog for text editors.
 */
class CKEditorEntityLinkDialog extends FormBase implements BaseFormIdInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor_entity_link_dialog';
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    // Use the EditorLinkDialog form id to ease alteration.
    return 'editor_link_dialog';
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\filter\Entity\FilterFormat $filter_format
   *   The filter format for which this dialog corresponds.
   */
  public function buildForm(array $form, FormStateInterface $form_state, FilterFormat $filter_format = NULL) {
    $config = $this->config('ckeditor_entity_link.settings');

    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    $user_input = $form_state->getUserInput();
    $input = isset($user_input['editor_object']) ? $user_input['editor_object'] : array();

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="ckeditor-entity-link-dialog-form">';
    $form['#suffix'] = '</div>';

    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $types = array();
    foreach ($config->get('entity_types') as $type => $selected) {
      if ($selected) {
        $types[$type] = $entity_types[$type]->getLabel();
      }
    }

    $form['entity_type'] = array(
      '#type' => 'select',
      '#title' => t('Link type'),
      '#options' => $types,
      '#default_value' => 'node',
      '#required' => TRUE,
      '#size' => 1,
      '#ajax' => array(
        'callback' => '::updateTypeSettings',
        'effect' => 'fade',
      ),
    );

    $entity_type = empty($form_state->getValue('entity_type')) ? 'node' : $form_state->getValue('entity_type');
    $bundles = array();
    foreach ($config->get($entity_type . '_bundles') as $bundle => $selected) {
      if ($selected) {
        $bundles[] = $bundle;
      }
    }

    $form['entity_id'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => $entity_type,
      '#title' => t('Link'),
      '#required' => TRUE,
      '#prefix' => '<div id="entity-id-wrapper">',
      '#suffix' => '</div>',
    );

    if (!empty($bundles)) {
      $form['entity_id']['#selection_settings']['target_bundles'] = $bundles;
    }

    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['save_modal'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => '::submitForm',
        'event' => 'click',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#ckeditor-entity-link-dialog-form', $form));
    }
    else {
      $entity = \Drupal::entityManager()
        ->getStorage( $form_state->getValue('entity_type'))
        ->load( $form_state->getValue('entity_id'));

      $values = array(
        'attributes' => array(
          'href' => $this->getUrl($entity),
        ) + $form_state->getValue('attributes', [])
      );

      $response->addCommand(new EditorDialogSave($values));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * Helper function to return entity url.
   *
   * @param EntityInterface $entity
   *
   * @return string
   *   Entity url.
   */
  public function getUrl(EntityInterface $entity) {
    switch ($entity->getEntityType()->get('id')) {
      case 'menu_link_content':
        return $entity->getUrlObject()->toString();
      case 'shortcut':
        return $entity->getUrl()->toString();
      default:
        return $entity->url();
    }
  }

  /**
   * Ajax callback to update the form fields which depend on embed type.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return AjaxResponse
   *   Ajax response with updated options for the embed type.
   */
  public function updateTypeSettings(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Update options for entity type bundles.
    $response->addCommand(new ReplaceCommand(
      '#entity-id-wrapper',
      $form['entity_id']
    ));

    return $response;
  }
}
