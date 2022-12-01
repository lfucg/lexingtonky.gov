<?php

namespace Drupal\custom_migrate\Event;

/**
 * @file
 * Contains \Drupal\migrate\Event\MigrateMapDeleteEvent.
 */

use Drupal\migrate_plus\Event\MigrateEvents;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Migrating events for d8 migration.
 */
class MigrateEvent implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public function getSubscribedEvents() {
    $events[MigrateEvents::PREPARE_ROW][] = ['onPrepareRow', 0];
    return $events;
  }

  /**
   * React to a new row.
   *
   * @param \Drupal\migrate_plus\Event\MigratePrepareRowEvent $event
   *   The prepare-row event.
   */
  public function onPrepareRow(MigratePrepareRowEvent $event) {
    $row = $event->getRow();

    $row->setSourceProperty('first_last', $row->getSourceProperty('first_name') . ' ' . $row->getSourceProperty('last_name'));
  }

}
