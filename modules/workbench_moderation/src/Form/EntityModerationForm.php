<?php
/**
 * @file
 * Contains \Drupal\workbench_moderation\Form\EntityModerationForm.
 */


namespace Drupal\workbench_moderation\Form;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workbench_moderation\Entity\ModerationState;
use Drupal\workbench_moderation\Entity\ModerationStateTransition;
use Drupal\workbench_moderation\ModerationInformationInterface;
use Drupal\workbench_moderation\StateTransitionValidation;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityModerationForm extends FormBase {

  /**
   * @var \Drupal\workbench_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  /**
   * @var \Drupal\workbench_moderation\StateTransitionValidation
   */
  protected $validation;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(ModerationInformationInterface $moderation_info, StateTransitionValidation $validation, EntityTypeManagerInterface $entity_type_manager) {
    $this->moderationInfo = $moderation_info;
    $this->validation = $validation;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('workbench_moderation.moderation_information'),
      $container->get('workbench_moderation.state_transition_validation'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'workbench_moderation_entity_moderation_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContentEntityInterface $entity = NULL) {
    /** @var ModerationState $current_state */
    $current_state = $entity->moderation_state->entity;

    $transitions = $this->validation->getValidTransitions($entity, $this->currentUser());

    // Exclude self-transitions.
    $transitions = array_filter($transitions, function(ModerationStateTransition $transition) use ($current_state) {
      return $transition->getToState() != $current_state->id();
    });

    $target_states = [];
    /** @var ModerationStateTransition $transition */
    foreach ($transitions as $transition) {
      $target_states[$transition->getToState()] = $transition->label();
    }

    if ($current_state) {
      $form['current'] = [
        '#type' => 'item',
        '#title' => $this->t('Status'),
        '#markup' => $current_state->label(),
      ];
    }

    // Persist the entity so we can access it in the submit handler.
    $form_state->set('entity', $entity);

    $form['new_state'] = [
      '#type' => 'select',
      '#title' => $this->t('Moderate'),
      '#options' => $target_states,
    ];

    $form['revision_log'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Log message'),
      '#size' => 30,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
    ];

    $form['#theme'] = ['entity_moderation_form'];

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var ContentEntityInterface $entity */
    $entity = $form_state->get('entity');

    $new_state = $form_state->getValue('new_state');
    $entity->moderation_state->target_id = $new_state;

    $entity->revision_log = $form_state->getValue('revision_log');

    $entity->save();

    drupal_set_message($this->t('The moderation state has been updated.'));

    /** @var ModerationState $state */
    $state = $this->entityTypeManager->getStorage('moderation_state')->load($new_state);

    // The page we're on likely won't be visible if we just set the entity to
    // the default state, as we hide that latest-revision tab if there is no
    // forward revision. Redirect to the canonical URL instead, since that will
    // still exist.
    if ($state->isDefaultRevisionState()) {
      $form_state->setRedirectUrl($entity->toUrl('canonical'));
    }
  }
}
