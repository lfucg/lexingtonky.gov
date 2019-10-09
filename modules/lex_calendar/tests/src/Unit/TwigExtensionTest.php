<?php

namespace Drupal\Tests\lex_calendar\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\lex_calendar\TwigExtension;

/**
 * Tests Full Calendar TwigExtension
 *
 * @coversDefaultClass \Drupal\lex_calendar\TwigExtension
 * @group lex_calendar
 */
class TwigExtensionTest extends UnitTestCase {

  public function testConstructor() {
    $extension = new TwigExtension();
    $this->assertTrue($extension instanceof \Twig_ExtensionInterface);
  }

  /**
   * Tests the Time Range Parser
   *
   * @dataProvider eventTimeRangeProvider
   */
  public function testEventTimeRangeParser($start, $end, $allDay, $expected) {
    $ext = new TwigExtension();
    $ext->setNow('2017-02-15T12:00');
    $this->assertEquals($expected, $ext->getLexTimeRange([
      'start' => $start,
      'end' => $end,
      'allDay' => $allDay
    ]));

  }

  public function eventTimeRangeProvider() {
    return [
      ['2017-06-08T11:30', '', 0, '11:30 a.m.'],
      ['2017-06-08T11:30', '', 1, 'All Day'],
      ['2017-06-08T11:30', '2017-06-08T12:30', 0, '11:30 a.m. &#8211; 12:30 p.m.'],
      ['2017-06-08T11:30', '2017-06-08T12:00', 0, '11:30 a.m. &#8211; noon'],
      ['2017-06-08T10:30', '2017-06-08T11:00', 0, '10:30 &#8211; 11 a.m.'],
      ['2017-06-08T13:00', '2017-06-08T14:00', 0, '1 &#8211; 2 p.m.']
    ];
  }

  /**
   * Tests the event Time Parser
   *
   * @dataProvider eventTimesProvider
   */
  public function testEventTimeParser ($start, $end, $allDay, $recurring, $expected) {
    $ext = new TwigExtension();
    $ext->setNow('2017-02-15T12:00');
    $this->assertEquals($expected, $ext->parseEventTimeString($start, $end, $allDay, $recurring,'2017-02-15T15:00'));
  }

  public function eventTimesProvider() {
    return [
      ['1975-06-08T05:00', '', 0, '', 'June 8, 1975, midnight'],
      ['1975-06-08T07:00', '', 0, '', 'June 8, 1975, 2 a.m.'],
      ['1975-06-08T17:00', '', 0, '', 'June 8, 1975, noon'],
      ['1975-06-08T07:30', '', 0, '', 'June 8, 1975, 2:30 a.m.'],
      ['2017-06-08T07:00', '', 0, '', 'June 8, 2 a.m.'],
      ['2017-06-08T07:30', '', 0, '', 'June 8, 2:30 a.m.'],
      ['1975-01-08T06:00', '', 0, '', 'Jan. 8, 1975, 2 a.m.'],

      ['1975-06-08T05:00', '', 0, 'No', 'June 8, 1975, midnight'],
      ['1975-06-08T07:00', '', 0, 'No', 'June 8, 1975, 2 a.m.'],
      ['1975-06-08T17:00', '', 0, 'No', 'June 8, 1975, noon'],
      ['1975-06-08T07:30', '', 0, 'No', 'June 8, 1975, 2:30 a.m.'],
      ['2017-06-08T07:00', '', 0, 'No', 'June 8, 2 a.m.'],
      ['2017-06-08T07:30', '', 0, 'No', 'June 8, 2:30 a.m.'],
      ['1975-01-08T06:00', '', 0, 'No', 'Jan. 8, 1975, 2 a.m.'],

      ['1975-06-08T05:00', '1975-06-08T06:00', 0, '', 'June 8, 1975, midnight &#8211; 1 a.m.'],
      ['1975-06-08T07:00', '1975-06-08T08:00', 0, '', 'June 8, 1975, 2 &#8211; 3 a.m.'],
      ['1975-06-08T17:00', '1975-06-08T18:00', 0, '', 'June 8, 1975, noon &#8211; 1 p.m.'],
      ['1975-06-08T07:30', '1975-06-08T08:30', 0, '', 'June 8, 1975, 2:30 &#8211; 3:30 a.m.'],
      ['1975-06-08T07:00', '1975-06-08T07:00', 0, '', 'June 8, 1975, 2 a.m.'],
      ['2017-06-08T07:00', '2017-06-08T07:00', 0, '', 'June 8, 2 a.m.'],
      ['2017-06-08T07:00', '2017-06-08T08:00', 0, '', 'June 8, 2 &#8211; 3 a.m.'],
      ['2017-06-08T07:30', '2017-06-08T08:30', 0, '', 'June 8, 2:30 &#8211; 3:30 a.m.'],

      ['1975-06-09T04:00', '1975-06-09T05:00', 0, '', 'June 8, 1975, 11 p.m. &#8211; midnight'],
      ['1975-06-08T16:00', '1975-06-08T17:00', 0, '', 'June 8, 1975, 11 a.m. &#8211; noon'],
      ['1975-06-08T16:00', '1975-06-08T18:00', 0, '', 'June 8, 1975, 11 a.m. &#8211; 1 p.m.'],
      ['2017-06-09T04:00', '2017-06-09T05:00', 0, '', 'June 8, 11 p.m. &#8211; midnight'],
      ['2017-06-08T16:00', '2017-06-08T17:00', 0, '', 'June 8, 11 a.m. &#8211; noon'],
      ['2017-06-08T16:00', '2017-06-08T18:00', 0, '', 'June 8, 11 a.m. &#8211; 1 p.m.'],
      ['2017-06-08T16:30', '2017-06-08T18:30', 0, '', 'June 8, 11:30 a.m. &#8211; 1:30 p.m.'],
      ['2017-06-08T16:30', '2017-06-08T17:30', 0, '', 'June 8, 11:30 a.m. &#8211; 12:30 p.m.'],

      ['2017-06-08T06:00', '', 1, '', 'June 8'],
      ['1975-06-08T06:00', '', 1, '', 'June 8, 1975'],
      ['2017-06-08T06:00', '2017-06-08T07:00', 1, '', 'June 8'],
      ['2017-06-08T06:00', '2017-06-10T07:00', 1, '', 'June 8 &#8211; 10'],
      ['2017-06-08T06:00', '2017-07-01T07:00', 1, '', 'June 8 &#8211; July 1'],
      ['2017-12-31T12:00', '2018-01-01T12:00', 1, '', 'Dec. 31 &#8211; Jan. 1, 2018'],
      ['2016-12-31T12:00', '2017-01-01T12:00', 1, '', 'Dec. 31, 2016 &#8211; Jan. 1'],

      ['2017-02-01T17:00', '', 0, 'Weekly', 'Every Wednesday, 1 p.m.'],
      ['2017-02-01T16:00', '2017-02-01T17:00', 0, 'Weekly', 'Every Wednesday, noon &#8211; 1 p.m.'],
      ['2017-02-01T17:30', '2017-02-01T18:30', 0, 'Weekly', 'Every Wednesday, 1:30 &#8211; 2:30 p.m.'],
      ['2017-02-01T16:00', '2017-03-01T16:00', 0, 'Weekly', 'Every Wednesday, noon, Feb. 1 &#8211; March 1'],
      ['2017-02-01T17:00', '2017-03-01T17:00', 0, 'Weekly', 'Every Wednesday, 1 p.m., Feb. 1 &#8211; March 1'],
      ['2017-02-01T17:30', '2017-03-01T17:30', 0, 'Weekly', 'Every Wednesday, 1:30 p.m., Feb. 1 &#8211; March 1'],
      ['2017-07-01T17:00', '2017-07-29T17:00', 0, 'Weekly', 'Every Saturday, noon, July 1 &#8211; 29'],
      ['2017-07-01T18:00', '2017-07-29T18:00', 0, 'Weekly', 'Every Saturday, 1 p.m., July 1 &#8211; 29'],
      ['2017-07-01T18:30', '2017-07-29T18:30', 0, 'Weekly', 'Every Saturday, 1:30 p.m., July 1 &#8211; 29'],
      ['2017-02-01T16:00', '2017-03-01T17:00', 0, 'Weekly', 'Every Wednesday, noon &#8211; 1 p.m., Feb. 1 &#8211; March 1'],
      ['2017-02-01T17:30', '2017-03-01T18:30', 0, 'Weekly', 'Every Wednesday, 1:30 &#8211; 2:30 p.m., Feb. 1 &#8211; March 1'],
      ['2017-07-01T17:00', '2017-07-29T18:00', 0, 'Weekly', 'Every Saturday, noon &#8211; 1 p.m., July 1 &#8211; 29'],
      ['2017-07-01T18:30', '2017-07-29T19:30', 0, 'Weekly', 'Every Saturday, 1:30 &#8211; 2:30 p.m., July 1 &#8211; 29'],

      ['2017-02-01T16:00', '', 0, 'Monthly', 'First Wednesdays, noon'],
      ['2017-02-08T17:30', '2017-03-08T18:30', 0, 'Monthly', 'Second Wednesdays, 1:30 &#8211; 2:30 p.m., Feb. &#8211; March'],
      ['2017-02-15T17:30', '2017-03-15T18:30', 0, 'Monthly', 'Third Wednesdays, 1:30 &#8211; 2:30 p.m., Feb. &#8211; March'],
      ['2017-02-22T17:30', '2017-03-22T18:30', 0, 'Monthly', 'Fourth Wednesdays, 1:30 &#8211; 2:30 p.m., Feb. &#8211; March']
    ];
  }

}
