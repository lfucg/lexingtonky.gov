<?php

/**
 * @file
 * Contains \Drupal\workbench_moderation\Form\ModerationStateForm.
 */

namespace Drupal\workbench_moderation\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ModerationStateForm.
 *
 * @package Drupal\workbench_moderation\Form
 */
class ModerationStateForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /* @var \Drupal\workbench_moderation\ModerationStateInterface $moderation_state */
    $moderation_state = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $moderation_state->label(),
      '#description' => $this->t("Label for the Moderation state."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $moderation_state->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\workbench_moderation\Entity\ModerationState::load',
      ),
      '#disabled' => !$moderation_state->isNew(),
    );

    $form['published'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Published'),
      '#description' => $this->t('When content reaches this state it should be published.'),
      '#default_value' => $moderation_state->isPublishedState(),
    ];

    $form['default_revision'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Default revision'),
      '#description' => $this->t('When content reaches this state it should be made the default revision; this is implied for published states.'),
      '#default_value' => $moderation_state->isDefaultRevisionState(),
      // @todo When these are added, the checkbox default value does not apply properly.
      // @see https://www.drupal.org/node/2645614
      // '#states' => [
      //   'checked' => [':input[name="published"]' => ['checked' => TRUE]],
      //   'disabled' => [':input[name="published"]' => ['checked' => TRUE]],
      // ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $moderation_state = $this->entity;
    $status = $moderation_state->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Moderation state.', [
          '%label' => $moderation_state->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Moderation state.', [
          '%label' => $moderation_state->label(),
        ]));
    }
    $form_state->setRedirectUrl($moderation_state->urlInfo('collection'));
  }

}
