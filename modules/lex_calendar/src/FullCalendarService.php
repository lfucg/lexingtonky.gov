<?php

namespace Drupal\lex_calendar;

use Drupal\node\Entity\Node;

/**
 * Translates Drupal node collections into fullcalendar.js data.
 */
class FullCalendarService {
  /**
   * Collection of events that the controller will put into a JSON response.
   *
   * @var array
   */
  protected $events = [];

  /**
   * The start of the event seek range.
   *
   * @var \DateTime Object
   */
  protected $start = NULL;

  /**
   * The end of the event seek range.
   *
   * @var \DateTime Object
   */
  protected $end = NULL;

  /**
   * Setter.
   *
   * @param \DateTime object | string $date
   *   Object or string parsable to a date.
   */
  public function setStart($date) {
    if (!$date instanceof \DateTime) {
      $date = new \DateTime($date, new \DateTimeZone('America/New_York'));
    }
    $this->start = $date;
  }

  /**
   * Getter.
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * Setter.
   *
   * @param \DateTime object | string $date
   *   Object or string parsable to a date.
   */
  public function setEnd($date) {
    if (!$date instanceof \DateTime) {
      $date = new \DateTime($date, new \DateTimeZone('America/New_York'));
    }
    $this->end = $date;
  }

  /**
   * Getter.
   */
  public function getEnd() {
    return $this->end;
  }

  /**
   * Getter.
   */
  public function getEvents() {
    return $this->events;
  }

  /**
   * Translate node event data to fullcalendar.js format and add it.
   *
   * @param array $events
   *   Collection of events.
   */
  public function addEvents(array $events) {
    foreach ($events as $event) {
      /*
       * Skip any recurrings that happen to fall in the range.
       */
      if ($event->field_recurring_event->value === NULL || $event->field_recurring_event->value === 'No') {
        $this->addEvent($event,
          $this->cleanDate($event->field_date)->format('Y-m-d H:i:s'),
          $this->getEndEvent($event)->format('Y-m-d H:i:s')
        );
      }
    }
  }

  /**
   * Clean the date from Drupal's screwed up system to something useable.
   */
  protected function cleanDate($date) {
    $date = new \DateTime($date->value);
    // Ugly hack to adjust the time from UTC to Eastern, depending on the
    // timezone.
    $this->timeZoneAdjust($date);
    return $date;
  }

  protected function timeZoneAdjust(&$date) {
    ($date->format('I') == 1) ? $date->modify('-4 hours') : $date->modify('-5 hours');
  }

  /**
   * Apply corrections to the event data.
   */
  protected function getEndEvent($event) {
    if (empty($event->field_date_end->value)) {
      $date = new \DateTime($event->field_date->value);
      $date->modify('+3 hours');
      $this->timeZoneAdjust($date);
      return $date;
     }
     else {
      return $this->cleanDate($event->field_date_end);
     }
  }

  /**
   * Add a single event to the JSON array.
   *
   * @param $event
   *   Node Object.
   * @param string $start
   *   Start day and time for event.
   * @param string $end
   *   End day and time for event.
   */
  protected function addEvent($event, $start, $end) {
    if ($start >= $this->start->format('Y-m-d') && $end <= $this->end->format('Y-m-d')) {
      $this->events[] = [
        'allDay' => (bool) $event->field_all_day->value,
        'title' => $event->title->value,
        'id' => $event->nid->value,
        'end' => $end,
        'start' => $start,
        'url' => $event->url(),
        'description' => $event->body->value
      ];
    }
  }

  /**
   * Translate node event data to fullcalendar.js format and add it.
   *
   * We also replicate the event as many times as necessary to occupy all
   * available spots in the requested range that the event is to recur on.
   *
   * @param Node[] $events
   *   Collection of events.
   */
  public function addRecurringEvents(array $events) {
    foreach ($events as $event) {
      $start = $this->cleanDate($event->field_date);
      $end = $this->getEndEvent($event);
      $startTime = $start->format('H:i:s');
      $endTime = $end->format('H:i:s');
      // Lowercase L.
      $dayOfWeek = $start->format('l');
      $weekOfMonth = ceil($start->format('j') / 7);
      $interval = $event->field_recurring_event->value;

      /*
       * Now if an event set to recur starts and ends on the same day
       * it recurs indefinitely. So we will get all of the days with the same
       * day of the week as the start and return them.
       */
      if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
        $end = $this->end;

        if ($start->format('m') !== $this->start->format('m')) {
          $start = $this->start;

          if ($start->format('l') !== $dayOfWeek) {
            $start->modify("+1 $dayOfWeek");
          }
        }
      }

      $date = $start;

      /*
       * Monthly recurrences are on the same day of the week and week of the
       * month. We can find the correct week by taking ceil on the date / 7.
       * Either way we search for candidates for recurrance on each week whether
       * we write a recurring event to the result set or not.
       */
      do {
        if ($interval == 'Weekly' || ceil($date->format('j') / 7) === $weekOfMonth) {
          $this->addEvent($event, $date->format('Y-m-d') . ' ' . $startTime, $date->format('Y-m-d') . ' ' . $endTime);
        }
        $date->modify('+1 week');
      } while ($date->format('Y-m-d') <= $end->format('Y-m-d'));
    }
  }

  public function clear() {
    $this->events = [];
  }

  public function sort() {
    $start = [];

    foreach ($this->events as $key => $row) {
      $start[$key] = $row['start'];
    }

    return array_multisort($start, SORT_ASC, $this->events);
  }

}
