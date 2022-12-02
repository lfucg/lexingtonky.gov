<?php

namespace Drupal\lex_calendar;

/**
 * Event trait for lex calendar.
 */
trait EventFetch {
  /**
   * Custom service to translate Drupal nodes to full calendar event data.
   *
   * @var Drupal\lex_calendar\FullCalendarService
   */
  protected $events = NULL;

  /**
   * Query a range of events and send into the event service for parsing.
   *
   * @param string $contentType
   *   Event type to get, 'event' or 'meeting'.
   * @param string $start
   *   Start of the seek range.
   * @param string $end
   *   End of the seek range.
   */
  protected function queryEvents($contentType, $start, $end) {
    $this->events->setStart($start);
    $this->events->setEnd($end);

    /*
     * Get the non recurring events for the range.
     */
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', $contentType)
      ->condition('field_date', $this->events->getStart()->format('Y-m-d'), '>=');

    $query = $this->modifyEventQuery($query);

    $this->events->addEvents($this->entityTypeManager->getStorage('node')->loadMultiple($query->execute()));

    /*
     * And now the recurring events, which are handled by a seperate process
     * which duplicates them according to need.
     */
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', $contentType)
      ->condition('field_recurring_event', ['Weekly', 'Monthly'], 'IN');

    $query = $this->modifyEventQuery($query);

    $this->events->addRecurringEvents($this->entityTypeManager->getStorage('node')->loadMultiple($query->execute()));
  }

  /**
   * Allow implementing classes to add additional conditions.
   *
   * @param mixed $query
   *   Modifying the query.
   *
   * @return mixed
   *   Returns the query.
   */
  protected function modifyEventQuery($query) {
    return $query;
  }

}
