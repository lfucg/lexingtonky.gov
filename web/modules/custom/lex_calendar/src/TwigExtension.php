<?php

namespace Drupal\lex_calendar;

/**
 * Contains Twig extensions for the Lexington Calendar displays.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * Start of the range to be displayed.
   *
   * @var \DateTimeInterface
   */
  protected $start;

  /**
   * End of the range to be displayed.
   *
   * @var \DateTimeInterface
   */
  protected $end;

  /**
   * Flag of whether the event is all day and time display is to be suppressed.
   *
   * @var boolean
   */
  protected $allDay;

  /**
   * String describing the recurrance rate.
   *
   * @var string
   */
  protected $recurring;

  /**
   * Current time.
   *
   * @var \DateTimeInterface
   */
  protected $now;

  /**
   * Month list.
   *
   * Some are to be abbreviated, some not.
   */
  protected $months = [
    'Jan.',
    'Feb.',
    'March',
    'April',
    'May',
    'June',
    'July',
    'Aug.',
    'Sept.',
    'Oct.',
    'Nov.',
    'Dec.'
  ];

  /**
   * Create a TwigExtension object for the lex_calendar module.
   */
  public function __construct() {
    $this->setNow('now');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'lex_calendar';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('lexDateTime', [$this, 'getLexDateTime'], ['is_safe' => ['html']]),
      new \Twig_SimpleFunction('lexDate', [$this, 'getLexDate'], ['is_safe' => ['html']]),
      new \Twig_SimpleFunction('lexTimeRange', [$this, 'getLexTimeRange'], ['is_safe' => ['html']]),
      new \Twig_SimpleFunction('lexRender', [$this, 'getRender', ['is_safe' => 'html']])
    ];
  }

  public function getRender($field) {

    $entity = $field->entity;

    $render_controller = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());

    return $render_controller->view($entity);



   /*$build = [];
    $entity_type = $this->getDerivativeId();
    $entity = $this->routeMatch->getParameter($entity_type);

    if ($entity instanceof ContentEntityInterface) {
      $build['field'] = $entity->get($this->configuration['field_name'])->view([
        'label' => 'hidden',
        'type' => $this->configuration['formatter_id'],
        'settings' => $this->configuration['formatter_settings']
      ]);
      if ($this->configuration['label_from_field'] && !empty($build['field']['#title'])) {
        $build['#title'] = $build['field']['#title'];
      }
    }

    return $build;*/
  }

  /**
   * Return a Time range for an event.
   */
  public function getLexTimeRange($event) {
    if ($event['allDay']) {
      return "All Day";
    }

    $start = new \DateTime($event['start']);
    $end = new \DateTime($event['end']);

    if ($event['start'] == $event['end'] || empty($event['end'])) {
      return $this->getTime($start) . $this->appendMerdiem($start);
    }
    elseif ($start->format('a') === $end->format('a')){
      return $this->getTime($start) . ' - ' . $this->getTime($end) . $this->appendMerdiem($end);
    }

    return $this->getTime($start) . $this->appendMerdiem($start) . ' - ' . $this->getTime($end) . $this->appendMerdiem($end);
  }

  /**
   * Returns just the date.
   *
   * This is used by the home page event view blocks.
   */
  public function getLexDate($date) {
    if (!$date instanceof \DateTimeInterface) {
      if (is_numeric($date)) {
        $date = \DateTime::createFromFormat( 'U', $date);
      }
      else {
        $date = new \DateTime($date);
      }
    }
    return $date->format('l') . ', ' . $this->getDate($date);
  }

  /**
   * Returns the event start and end times for a given string.
   */
  public function getLexDateTime($event) {
    if (isset($event['field_date']) && isset($event['field_date']['#object'])) {
      return $this->parseEventTimeString(
        $event['field_date']['#object']->field_date->value,
        $event['field_date']['#object']->field_date_end->value,
        $event['field_date']['#object']->field_all_day->value,
        $event['field_date']['#object']->field_recurring_event->value
      );
    }
    else {
      return '';
    }
  }

  /**
   * Return the year if it is different from now's year.
   *
   * @param \DateTimeInterface $date
   *   The Date to parse.
   *
   * @return string
   *   Date string snippet.
   */
  protected function getYear(\DateTimeInterface $date) {
    return $date->format('Y');
  }

  /**
   * Set Now.
   *
   * @param string $now
   *   A parsable date string.
   */
  public function setNow($now) {
    $this->now = new \DateTime($now);
  }

  /**
   * Parse the event start and end times.
   *
   * @param string $start
   *   A parsable date string for the start of the range to display.
   * @param string $end
   *   A parsable date string for the end of the range, or empty string.
   * @param string $allDay
   *   Flag for if the event is all day. Will be 1 or 0.
   * @param string $recurring
   *   Recurrance.  Will be one of none, no, Weekly or Monthly.
   *
   * @return string
   *   An Associated Press conformant Date String.
   */
  public function parseEventTimeString($start, $end, $allDay, $recurring) {
    $this->start = new \DateTime($start);
    $this->allDay = (boolean) $allDay;

    switch ($recurring) {
      case 'Weekly':
        $this->recurring = 'Weekly';
      break;
      case 'Monthly':
        $this->recurring = 'Monthly';
      break;
      default:
        $this->recurring = '';
      break;
    }

    $this->end = empty($end) ? NULL : new \DateTime($end);
    $this->timezoneAdjust();
    return $this->end === NULL || $this->start == $this->end ? $this->parseSingleDate() : $this->parseRange();
  }

  /**
   * Adjust the dates for the Timezone.
   *
   * Drupal is currently storing the dates in UTC format with no information
   * for timezone adjustment. If that ever gets fixed this function will need
   * to be revisited.
   */
  protected function timezoneAdjust() {
    $this->start->format('I') === '0' ?
      $this->start->modify('-5 hours') :
      $this->start->modify('-4 hours');

    if ($this->end instanceof \DateTimeInterface) {
      $this->end->format('I') === '0' ?
        $this->end->modify('-5 hours') :
        $this->end->modify('-4 hours');
    }
  }

  /**
   * Get the recurrence string.
   *
   * @return string
   *   Date string snippet.
   */
  protected function getRecurrence() {
    $return = '';
    if ($this->recurring === 'Weekly') {
      $return = 'Every ';
    }
    else {
      $ords = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth'];
      $return .= $ords[ceil($this->start->format('j') / 7) - 1] . ' ';
    }
    // Lowercase L.
    return $return . $this->start->format('l') . ($this->recurring === 'Monthly' ? 's' : '');
  }

  /**
   * Parse out the snippet for a single date.
   *
   * @return string
   *   Date string snippet.
   */
  protected function parseSingleDate() {
    $return = $this->recurring ? $this->getRecurrence() : $this->getDate($this->start);

    if (!$this->allDay) {
      $return .= ', ' . $this->getTime($this->start) . $this->appendMerdiem($this->start);
    }

    return $return;
  }

  /**
   * Gets the Month, Day format, and also the year if it's not current.
   *
   * @param \DateTimeInterface $date
   *   The date to parse.
   *
   * @return string
   *   Date string snippet.
   */
  protected function getDate(\DateTimeInterface $date) {
    $return = $this->getMonthDay($date);
    $year = $this->getYear($date);

    if ($year) {
      $return .= ', ' . $year;
    }

    return $return;
  }

  protected function getMonthDay(\DateTimeInterface $date) {
    return $this->months[$date->format('n') - 1] . $date->format(' j');
  }

  /**
   * Get the time string.
   *
   * Minutes will only be included if they aren't zero. 12 will be noon or
   * midnight as appropriate.
   *
   * @param \DateTimeInterface $date
   *   The date to parse.
   *
   * @return string
   *   Date string snippet.
   */
  protected function getTime(\DateTimeInterface $date) {
    $hour = $date->format('g');
    $minutes = $date->format('i');
    $minutes = $minutes === '00' ? '' : ':' . $minutes;

    if ($hour === '12' && $minutes === '') {
      return $date->format('a') === 'am' ? 'midnight' : 'noon';
    }
    else {
      return $hour . $minutes;
    }
  }

  /**
   * Returns the Merdiem string.
   *
   * @param \DateTimeInterface $date
   *   The date to parse.
   *
   * @return string
   *   Date string snippet.
   */
  protected function appendMerdiem(\DateTimeInterface $date) {
    $hour = $date->format('g');
    $minutes = $date->format('i');
    $minutes = $minutes === '00' ? '' : ':' . $minutes;

    if ($hour === '12' && $minutes === '') {
      return '';
    }
    else {
      return $date->format('a') === 'am' ? ' a.m.' : ' p.m.';
    }
  }

  /**
   * Return the Meridem for the start if it is different from the end's.
   *
   * @param \DateTimeInterface $start
   *   The start date to parse.
   * @param \DateTimeInterface $end
   *   The end date to parse.
   *
   * @return string
   *   Date string snippet.
   */
  protected function conditionalAppendMeridiem(\DateTimeInterface $start, \DateTimeInterface $end) {
    return $start->format('a') === $end->format('a') ? '' : $this->appendMerdiem($start);
  }

  /**
   * Parse a time or date range.
   *
   * @return string
   *   Date string snippet.
   */
  protected function parseRange() {
    if ($this->recurring) {
      return $this->parseRecurringRange();
    }
    else {
      $return = $this->start->format('M j');
      if (!$this->allDay) {
        $return .= ', ' . $this->start->format('Y') . ', ' . $this->getTime($this->start)
          . $this->conditionalAppendMeridiem($this->start, $this->end)
          . ' &#8211; ' . $this->getTime($this->end)
          . $this->appendMerdiem($this->end);
      }
      else {
        if ($this->start->format('M j') !== $this->end->format('M j')) {
          $return .= ' &#8211; ' . ($this->start->format('M') === $this->end->format('M') ? $this->end->format('j') : $this->getMonthDay($this->end));
        }

          $return .= ', ' . $this->start->format('Y');


      }

      return $return;
    }
  }

  /**
   * Parse a range of das or months.
   *
   * @return string
   *   Date string snippet.
   */
  protected function parseDayRange() {
    if ($this->start->format('M j') === $this->end->format('M j')) {
      return '';
    }
    elseif ($this->recurring === 'Monthly') {
      return ', ' . $this->months[$this->start->format('n') - 1] . ' &#8211; ' . $this->months[$this->end->format('n') - 1] . ', ' . $this->getYear($this->end);
    }
    else {
      return ', ' . $this->getMonthDay($this->start) . ' &#8211; '
        . ($this->start->format('M') === $this->end->format('M') ? $this->end->format('j') : $this->getMonthDay($this->end)) . ', ' . $this->getYear($this->end);
    }
  }

  /**
   * Parse a recurring range string.
   *
   * @return string
   *   Date string snippet.
   */
  protected function parseRecurringRange() {
    if ($this->start->format('H:i') === $this->end->format('H:i')) {
      $return = $this->parseSingleDate() . $this->parseDayRange();
    }
    else {
      $return = $this->getRecurrence()
        . ', ' . $this->getTime($this->start)
        . $this->conditionalAppendMeridiem($this->start, $this->end)
        . ' &#8211; ' . $this->getTime($this->end)
        . $this->appendMerdiem($this->end)
        . $this->parseDayRange();
    }
    return $return;
  }

}
