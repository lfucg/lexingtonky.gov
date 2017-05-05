<?php

namespace Drupal\lex_calendar;

trait EventFetch {
  /**
   * Custom service to translate Drupal nodes to full calendar event data.
   *
   * @var Drupal\lex_calendar\FullCalendarService object
   */
  protected $events = NULL;

  /**
   * Query a range of events and send into the event service for parsing.
   *
   * @param $contentType string
   *   Event type to get, 'event' or 'meeting'
   * @param $start string
   *   Start of the seek range.
   * @param $end string
   *   End of the seek range
   */
  protected function queryEvents($contentType, $start, $end) {
    $this->events->setStart($start);
    $this->events->setEnd($end);

    /*
     * Get the non recurring events for the range.
     */
    $query = $this->entityQuery->get('node')
      ->condition('status', 1)
      ->condition('type', $contentType)
      ->condition('field_date', $this->events->getStart()->format('Y-m-d'), '>=');

    $this->events->addEvents($this->entityManager->getStorage('node')->loadMultiple($query->execute()));

    /*
     * And now the recurring events, which are handled by a seperate process
     * which duplicates them according to need.
     */
    $query = $this->entityQuery->get('node')
      ->condition('status', 1)
      ->condition('type', $contentType)
      ->condition('field_recurring_event', ['Weekly', 'Monthly'], 'IN');

    $this->events->addRecurringEvents($this->entityManager->getStorage('node')->loadMultiple($query->execute()));
  }
}
