<?php

namespace Drupal\workbench_moderation\Form;


use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workbench_moderation\Entity\ModerationState;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for configuring moderation usage on a given entity bundle.
 */
class BundleModerationConfigurationForm extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @inheritDoc
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   *
   * We need to blank out the base form ID so that poorly written form alters
   * that use the base form ID to target both add and edit forms don't pick
   * up our form. This should be fixed in core.
   */
  public function getBaseFormId() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /* @var ConfigEntityTypeInterface $bundle */
    $bundle = $form_state->getFormObject()->getEntity();
    $form['enable_moderation_state'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable moderation states.'),
      '#description' => t('Content of this type must transition through moderation states in order to be published.'),
      '#default_value' => $bundle->getThirdPartySetting('workbench_moderation', 'enabled', FALSE),
    ];

    // Add a special message when moderation is being disabled.
    if ($bundle->getThirdPartySetting('workbench_moderation', 'enabled', FALSE)) {
      $form['enable_moderation_state_note'] = [
        '#type' => 'item',
        '#description' => t('After disabling moderation, any existing forward drafts will be accessible via the "Revisions" tab.'),
        '#states' => [
          'visible' => [
            ':input[name=enable_moderation_state]' => ['checked' => FALSE],
          ],
        ],
      ];
    }

    $states = \Drupal::entityTypeManager()->getStorage('moderation_state')->loadMultiple();
    $options = [];
    /** @var ModerationState $state */
    foreach ($states as $key => $state) {
      $options[$key] = $state->label() . ' ' . ($state->isPublishedState() ? t('(published)') : t('(non-published)'));
    }
    $form['allowed_moderation_states'] = [
      '#type' => 'checkboxes',
      '#title' => t('Allowed moderation states.'),
      '#description' => t('The allowed moderation states this content-type can be assigned. You must select at least one published and one non-published state.'),
      '#default_value' => $bundle->getThirdPartySetting('workbench_moderation', 'allowed_moderation_states', []),
      '#options' => $options,
      '#states' => [
        'visible' => [
          ':input[name=enable_moderation_state]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['default_moderation_state'] = [
      '#type' => 'select',
      '#title' => t('Default moderation state'),
      '#empty_option' => t('-- Select --'),
      '#options' => $options,
      '#description' => t('Select the moderation state for new content'),
      '#default_value' => $bundle->getThirdPartySetting('workbench_moderation', 'default_moderation_state', ''),
      '#states' => [
        'visible' => [
          ':input[name=enable_moderation_state]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['#entity_builders'][] = [$this, 'formBuilderCallback'];

    return parent::form($form, $form_state);
  }

  /**
   * Form builder callback.
   *
   * @todo I don't know why this needs to be separate from the form() method.
   * It was in the form_alter version but we should see if we can just fold
   * it into the method above.
   *
   * @param $entity_type
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $bundle
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function formBuilderCallback($entity_type, ConfigEntityInterface $bundle, &$form, FormStateInterface $form_state) {
    // @todo write a test for this.
    $bundle->setThirdPartySetting('workbench_moderation', 'enabled', $form_state->getValue('enable_moderation_state'));
    $bundle->setThirdPartySetting('workbench_moderation', 'allowed_moderation_states', array_keys(array_filter($form_state->getValue('allowed_moderation_states'))));
    $bundle->setThirdPartySetting('workbench_moderation', 'default_moderation_state', $form_state->getValue('default_moderation_state'));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo write a test for this.
    if ($form_state->getValue('enable_moderation_state')) {
      $states = $this->entityTypeManager->getStorage('moderation_state')->loadMultiple();
      $published = FALSE;
      $non_published = TRUE;
      $allowed = array_keys(array_filter($form_state->getValue('allowed_moderation_states')));
      foreach ($allowed as $state_id) {
        /** @var ModerationState $state */
        $state = $states[$state_id];
        if ($state->isPublishedState()) {
          $published = TRUE;
        }
        else {
          $non_published = TRUE;
        }
      }
      if (!$published || !$non_published) {
        $form_state->setErrorByName('allowed_moderation_states', t('You must select at least one published moderation and one non-published state.'));
      }
      if (($default = $form_state->getValue('default_moderation_state')) && !empty($default)) {
        if (!in_array($default, $allowed, TRUE)) {
          $form_state->setErrorByName('default_moderation_state', t('The default moderation state must be one of the allowed states.'));
        }
      }
      else {
        $form_state->setErrorByName('default_moderation_state', t('You must select a default moderation state.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // If moderation is enabled, revisions MUST be enabled as well.
    // Otherwise we can't have forward revisions.
    if($form_state->getValue('enable_moderation_state')) {
      /* @var ConfigEntityTypeInterface $bundle */
      $bundle = $form_state->getFormObject()->getEntity();

      $this->entityTypeManager->getHandler($bundle->getEntityType()->getBundleOf(), 'moderation')->onBundleModerationConfigurationFormSubmit($bundle);
    }

    parent::submitForm( $form, $form_state);

    drupal_set_message($this->t('Your settings have been saved.'));
  }
}
