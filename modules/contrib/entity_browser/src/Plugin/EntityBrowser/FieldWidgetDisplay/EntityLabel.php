<?php

namespace Drupal\entity_browser\Plugin\EntityBrowser\FieldWidgetDisplay;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_browser\FieldWidgetDisplayBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays a label of the entity.
 *
 * @EntityBrowserFieldWidgetDisplay(
 *   id = "label",
 *   label = @Translation("Entity label"),
 *   description = @Translation("Displays entity with a label.")
 * )
 */
class EntityLabel extends FieldWidgetDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity) {
    return $entity->label();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#markup' => $this->t('This plugin has no configuration options.'),
    ];
  }

}
