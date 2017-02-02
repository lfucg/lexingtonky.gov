<?php
/*
 * @file Contains Drupal\lex_calendar\FullCalendarService
 */
namespace Drupal\lex_calendar;

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
   * @var \DateTime Object
   */
  protected $end = NULL;

  /**
   * Setter.
   *
   * @param \DateTime object | string parsable to a date.
   */
  public function setStart($date) {
    if (!$date instanceof \DateTime) {
      $date = new \DateTime($date, new \DateTimeZone('America/New_York'));
    }
    $this->start = $date;
  }

  /**
   * Getter
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * Setter.
   *
   * @param \DateTime object | string parsable to a date.
   */
  public function setEnd($date) {
    if(!$date instanceof \DateTime) {
      $date = new \DateTime($date, new \DateTimeZone('America/New_York'));
    }
    $this->end = $date;
  }

  /**
   * Getter
   */
  public function getEnd() {
    return $this->end;
  }

  /**
   * Getter
   */
  public function getEvents() {
    return $this->events;
  }

  /**
   * Translate node event data to fullcalendar.js format and add it.
   *
   * @param array
   *   Collection of events.
   */
  public function addEvents(array $events) {
    foreach ($events as $event) {
      /*
       * Skip any recurrings that happen to fall in the range.
       */
      if( $event->field_recurring_event->value === NULL || $event->field_recurring_event->value === 'No') {
        $this->addEvent($event,
          str_replace('T', ' ', $event->field_date->value),
          str_replace('T', ' ', $event->field_date_end->value)
        );
      }
    }
  }

  /**
   * Add a single event to the JSON array.
   *
   * @param Entity
   *   Node Object
   * @param string
   *   Start day and time for event
   * @param string
   *   End day and time for event
   */
  protected function addEvent($event, $start, $end) {
    $this->events[] = [
      'allDay' => (bool) $event->field_all_day->value,
      'title' => $event->title->value,
      'id' => $event->nid->value,
      'end' => $end,
      'start' => $start,
      'url' => $event->url()
    ];
  }


  /**
   * Translate node event data to fullcalendar.js format and add it.
   *
   * We also replicate the event as many times as necessary to occupy all
   * available spots in the requested range that the event is to recur on.
   *
   * @param array
   *   Collection of events.
   *
   * @return Entity[]
   */
  public function addRecurringEvents(array $events) {
    foreach ($events as $event) {
      $start = new \DateTime(substr($event->field_date->value, 0, 10), new \DateTimeZone('America/New_York'));
      $end = new \DateTime(substr($event->field_date_end->value, 0, 10), new \DateTimeZone('America/New_York'));
      $startTime = substr($event->field_date->value, 11);
      $endTime = substr($event->field_date_end->value, 11);
      $dayOfWeek = $start->format('l'); // Lowercase L
      $weekOfMonth = ceil($start->format('j')/7);
      $interval = $event->field_recurring_event->value;

      /**
       * Now if an event set to recur starts and ends on the same day
       * it recurs indefinitely. So we will get all of the days with the same
       * day of the week as the start and return them.
       */
      if($start->format('Y-m-d') === $end->format('Y-m-d')) {
        $end = $this->end;

        if ($start->format('m') !== $this->start->format('m')) {
          $start = $this->start;

          if($start->format('l') !== $dayOfWeek) {
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
        if ($interval == 'Weekly' || ceil($date->format('j')/7) === $weekOfMonth) {
          $this->addEvent($event, $date->format('Y-m-d') . ' ' . $startTime, $date->format('Y-m-d') . ' ' . $endTime);
        }
        $date->modify('+1 week');
      } while ($date->format('Y-m-d') <= $end->format('Y-m-d'));
    }
  }
}
