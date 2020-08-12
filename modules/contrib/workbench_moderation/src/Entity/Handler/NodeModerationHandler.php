<?php
/**
 * @file
 * Contains Drupal\workbench_moderation\Entity\Handler\NodeCustomizations.
 */

namespace Drupal\workbench_moderation\Entity\Handler;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Customizations for node entities.
 */
class NodeModerationHandler extends ModerationHandler {

  /**
   * {@inheritdoc}
   */
  public function enforceRevisionsEntityFormAlter(array &$form, FormStateInterface $form_state, $form_id) {
    $form['revision']['#disabled'] = TRUE;
    $form['revision']['#default_value'] = TRUE;
    $form['revision']['#description'] = $this->t('Revisions are required.');
  }

  /**
   * {@inheritdoc}
   */
  public function enforceRevisionsBundleFormAlter(array &$form, FormStateInterface $form_state, $form_id) {
    /* @var \Drupal\node\Entity\NodeType $entity */
    $entity = $form_state->getFormObject()->getEntity();

    if ($entity->getThirdPartySetting('workbench_moderation', 'enabled', FALSE)) {
      // Force the revision checkbox on.
      $form['workflow']['options']['#default_value']['revision'] = 'revision';
      $form['workflow']['options']['revision']['#disabled'] = TRUE;
    }
  }

}
