<?php

/**
 * @file
 * Contains \Drupal\fieldblock\Plugin\Derivative\FieldBlockDeriver.
 */

namespace Drupal\fieldblock\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\fieldblock\Controller\FieldBlockController;

/**
 * Provides block plugin definitions for fieldblock blocks.
 *
 * @see \Drupal\fieldblock\Plugin\Block\FieldBlock
 */
class FieldBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a FieldBlockDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $fb_controller = new FieldBlockController();
    $definitions = $this->entityManager->getDefinitions();

    foreach ($definitions as $entity_type_id => $definition) {
      if ($fb_controller->isBlockableEntityType($definition)) {
        $this->derivatives[$entity_type_id] = $base_plugin_definition;
        $this->derivatives[$entity_type_id]['admin_label'] = $this->t('@type field', [
          '@type' => $definition->getLabel(),
        ]);
      }
    }

    return $this->derivatives;
  }

}
