<?php

namespace Drupal\draggableviews\Plugin\migrate\destination;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Row;

/**
 * Defines destination plugin for Draggableviews.
 *
 * @MigrateDestination(
 *   id = "draggableviews"
 * )
 */
class DraggableViews extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $record = [
      'view_name' => $row->getDestinationProperty('view_name'),
      'view_display' => $row->getDestinationProperty('view_display'),
      'args' => $row->getDestinationProperty('args'),
      'entity_id' => $row->getDestinationProperty('entity_id'),
      'weight' => $row->getDestinationProperty('weight'),
      'parent' => $row->getDestinationProperty('parent'),
    ];
    $result = Database::getConnection()->insert('draggableviews_structure')->fields($record)->execute();
    return [$result];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'dvid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    return [
      'dvid' => $this->t('The primarty identifier'),
      'view_name' => $this->t('The view name.'),
      'view_display' => $this->t('The view display.'),
      'args' => $this->t('The arguments.'),
      'entity_id' => $this->t('The entity id.'),
      'weight' => $this->t('The order weight.'),
      'parent' => $this->t('The parent entity id.'),
    ];
  }

}
