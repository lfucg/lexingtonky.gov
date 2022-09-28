<?php

namespace Drupal\workbench_moderation;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides the workbench_moderation views integration.
 */
class ViewsData {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The moderation information.
   *
   * @var \Drupal\workbench_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * Creates a new ViewsData instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\workbench_moderation\ModerationInformationInterface $moderation_information
   *   The moderation information.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModerationInformationInterface $moderation_information) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationInformation = $moderation_information;
  }

  /**
   * Returns the views data.
   *
   * @return array
   *   The views data.
   */
  public function getViewsData() {
    $data = [];

    $data['workbench_revision_tracker']['table']['group'] = $this->t('Workbench moderation');

    $data['workbench_revision_tracker']['entity_type'] = [
      'title' => $this->t('Entity type'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['workbench_revision_tracker']['entity_id'] = [
      'title' => $this->t('Entity ID'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['workbench_revision_tracker']['langcode'] = [
      'title' => $this->t('Entity language'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'language',
      ],
      'argument' => [
        'id' => 'language',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['workbench_revision_tracker']['revision_id'] = [
      'title' => $this->t('Latest revision ID'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    // Add a join for each entity type to the workbench_revision_tracker table.
    foreach ($this->moderationInformation->selectRevisionableEntities($this->entityTypeManager->getDefinitions()) as $entity_type_id => $entity_type) {
      /** @var \Drupal\views\EntityViewsDataInterface $views_data */
      // We need the views_data handler in order to get the table name later.
      if ($this->entityTypeManager->hasHandler($entity_type_id, 'views_data') && $views_data = $this->entityTypeManager->getHandler($entity_type_id, 'views_data')) {
        // Add a join from the entity base table to the revision tracker table.
        $base_table = $views_data->getViewsTableForEntityType($entity_type);
        $data['workbench_revision_tracker']['table']['join'][$base_table] = [
          'left_field' => $entity_type->getKey('id'),
          'field' => 'entity_id',
          'extra' => [
            [
              'field' => 'entity_type',
              'value' => $entity_type_id,
            ],
          ],
        ];

        // Some entity types might not be translatable.
        if ($entity_type->hasKey('langcode')) {
          $data['workbench_revision_tracker']['table']['join'][$base_table]['extra'][] = [
            'field' => 'langcode',
            'left_field' => $entity_type->getKey('langcode'),
            'operation' => '=',
          ];
        }

        // Add a relationship between the revision tracker table to the latest
        // revision on the entity revision table.
        $data['workbench_revision_tracker']['latest_revision__' . $entity_type_id] = [
          'title' => $this->t('@label latest revision', ['@label' => $entity_type->getLabel()]),
          'group' => $this->t('@label revision', ['@label' => $entity_type->getLabel()]),
          'relationship' => [
            'id' => 'standard',
            'label' => $this->t('@label latest revision', ['@label' => $entity_type->getLabel()]),
            'base' => $this->getRevisionViewsTableForEntityType($entity_type),
            'base field' => $entity_type->getKey('revision'),
            'relationship field' => 'revision_id',
            'extra' => [
              [
                'left_field' => 'entity_type',
                'value' => $entity_type_id,
              ],
            ],
          ],
        ];

        // Some entity types might not be translatable.
        if ($entity_type->hasKey('langcode')) {
          $data['workbench_revision_tracker']['latest_revision__' . $entity_type_id]['relationship']['extra'][] = [
            'left_field' => 'langcode',
            'field' => $entity_type->getKey('langcode'),
            'operation' => '=',
          ];
        }
      }
    }

    return $data;
  }

  /**
   * Gets the table of an entity type to be used as revision table in views.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return string
   *   The revision base table.
   */
  protected function getRevisionViewsTableForEntityType(EntityTypeInterface $entity_type) {
    return $entity_type->getRevisionDataTable() ?: $entity_type->getRevisionTable();
  }

}
