<?php

namespace Drupal\Tests\addtocal\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\addtocal\Controller\AddtocalController;

/**
 * Tests Add To Calendar ical widgetr.
 *
 * @coversDefaultClass \Drupal\addtocal\Controller\AddtocalController
 * @group addtocal
 */
class AddtocalControllerTest extends UnitTestCase {

	public function testFormatDate() {
		$pages = new AddtocalController();

		$expected = '20111027T202339Z';

		$this->assertEquals($expected, $pages->formatDate('2011-10-27T20:23:39'));
	}

}
