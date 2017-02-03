<?php

/**
 * @file Contains Drupal\Tests\lex_calendar\Unit\FullCalendarServiceTest.
 */
namespace Drupal\Tests\lex_calendar\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\lex_calendar\FullCalendarService;

/**
 * Tests Full Calendar JSON Composer.
 *
 * @coversDefaultClass \Drupal\lex_calendar\FullCalendarService
 * @group lex_calendar
 */
class FullCalendarServiceTest extends UnitTestCase {
  /**
   * Full Calendar Service object under test.
   *
   * @var Drupal/lex_calendar\FullCalendarService
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->service = new FullCalendarService();
    $this->service->setStart('2017-01-01');
    $this->service->setEnd('2017-01-31');
  }

  /**
   * Test the start and end getter/setter function pairs.
   */
  public function testDateSettersAndGetters() {
    $service = new FullCalendarService();
    $dateObject = new \DateTime('now', new \DateTimeZone('America/New_York'));
    $service->setStart($dateObject);
    $service->setEnd($dateObject);
    $this->assertEquals($dateObject, $service->getStart());
    $this->assertEquals($dateObject, $service->getEnd());
    $service->setStart('2017-01-01');
    $service->setEnd('2018-01-01');
    $this->assertEquals(new \DateTime('2017-01-01', new \DateTimeZone('America/New_York')), $service->getStart());
    $this->assertEquals(new \DateTime('2018-01-01', new \DateTimeZone('America/New_York')), $service->getEnd());
  }

  /**
   * Test adding non-recurring events.
   */
  public function testAddEvents() {

    /*
     * The recurring event in this group should be excluded from the result.
     */
    $this->service->addEvents([
      new MockNode([
        'nid' => '0',
        'field_date' => '2017-01-01T06:00:00',
        'field_date_end' => '2017-01-01T07:00:00',
        'field_all_day' => '0',
        'title' => 'Normal Event Recur NULL',
        'field_recurring_event' => NULL,
      ]),
      new MockNode([
        'nid' => '1',
        'field_date' => '2017-01-01T07:00:00',
        'field_date_end' => '2017-01-01T08:00:00',
        'field_all_day' => '1',
        'title' => 'Normal Event Recur No',
        'field_recurring_event' => 'No',
      ]),
      new MockNode([
        'nid' => '2',
        'field_date' => '2017-01-01T09:00:00',
        'field_date_end' => '2017-01-01T10:00:00',
        'field_all_day' => '1',
        'title' => 'Recurring Event should not show',
        'field_recurring_event' => 'Weekly',
      ]),
    ]);

    $this->assertEquals([
      [
        'allDay' => FALSE,
        'id' => '0',
        'end' => '2017-01-01 07:00:00',
        'start' => '2017-01-01 06:00:00',
        'title' => 'Normal Event Recur NULL',
        'url' => 'link',
      ],
      [
        'allDay' => TRUE,
        'id' => '1',
        'end' => '2017-01-01 08:00:00',
        'start' => '2017-01-01 07:00:00',
        'title' => 'Normal Event Recur No',
        'url' => 'link',
      ],
    ], $this->service->getEvents());
  }

  /**
   * Test adding a weekly recurring event that is indefinite in duration.
   */
  public function testAddRecurringEventsIndefinite() {
    $this->service->addRecurringEvents([
      new MockNode([
        'nid' => '0',
        'field_date' => '2017-01-03T06:00:00',
        'field_date_end' => '2017-01-03T07:00:00',
        'field_all_day' => '0',
        'title' => 'Normal Event Recur Weekly',
        'field_recurring_event' => 'Weekly',
      ]),
    ]);

    $events = $this->service->getEvents();

    $this->assertEquals(5, count($events));
    foreach ($events as $event) {
      $start = new \DateTime($event['start']);
      $this->assertEquals('Tuesday', $start->format('l'));
    }
  }

  /**
   * Test adding a weekly recurring event with a defined start and end.
   */
  public function testAddRecurringEventsDefinite() {
    $this->service->addRecurringEvents([
      new MockNode([
        'nid' => '0',
        'field_date' => '2017-01-04T06:00:00',
        'field_date_end' => '2017-01-18T07:00:00',
        'field_all_day' => '0',
        'title' => 'Normal Event Recur Weekly',
        'field_recurring_event' => 'Weekly',
      ]),
    ]);

    $events = $this->service->getEvents();

    $this->assertEquals(3, count($events));
    foreach ($events as $event) {
      $start = new \DateTime($event['start']);
      $this->assertEquals('Wednesday', $start->format('l'));
    }
  }

  /**
   * Test adding a monthly recurring event with an indefinite duration.
   */
  public function testAddRecurringEventsIndefiniteMonthly() {
    $this->service->setEnd('2017-03-31');
    $this->service->addRecurringEvents([
      new MockNode([
        'nid' => '0',
        'field_date' => '2017-01-04T06:00:00',
        'field_date_end' => '2017-01-04T07:00:00',
        'field_all_day' => '0',
        'title' => 'Normal Event Recur Monthly',
        'field_recurring_event' => 'Monthly',
      ]),
    ]);

    $events = $this->service->getEvents();

    $this->assertEquals(3, count($events));
    foreach ($events as $event) {
      $start = new \DateTime($event['start']);
      $this->assertEquals('Wednesday', $start->format('l'));
    }
  }

  /**
   * Test adding a monthly recurring event with a defined start and end.
   */
  public function testAddRecurringEventsDefiniteMonthly() {
    $this->service->setEnd('2017-03-31');
    $this->service->addRecurringEvents([
      new MockNode([
        'nid' => '0',
        'field_date' => '2017-01-04T06:00:00',
        'field_date_end' => '2017-02-01T07:00:00',
        'field_all_day' => '0',
        'title' => 'Normal Event Recur Monthly',
        'field_recurring_event' => 'Monthly',
      ]),
    ]);

    $events = $this->service->getEvents();

    $this->assertEquals(2, count($events));
    foreach ($events as $event) {
      $start = new \DateTime($event['start']);
      $this->assertEquals('Wednesday', $start->format('l'));
    }
  }

}

/**
 * Mock node object.
 *
 * Once we move to PHP 7 I change this to an anonymous class.
 */
class MockNode {
  protected $nid = NULL;
  protected $field_date = NULL;
  protected $field_date_end = NULL;
  protected $field_all_day = NULL;
  protected $title = NULL;
  protected $field_recurring_event = NULL;

  /**
   *
   */
  public function __construct($array) {
    foreach ($array as $key => $value) {
      $this->__set($key, $value);
    }
  }

  /**
   *
   */
  public function __get($key) {
    return $this->$key;
  }

  /**
   *
   */
  public function __set($key, $value) {
    $this->$key = new \stdClass();
    $this->$key->value = $value;
  }

  /**
   *
   */
  public function url() {
    return 'link';
  }

}
