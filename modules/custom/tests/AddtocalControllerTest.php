<?php

class AddtocalControllerTest extends PHPUnit_Framework_TestCase {

	public function testFormatDate() {
		$pages = new \Drupal\addtocal\Controller\AddtocalController();

		$expected = '20111027T202339Z';

		$this->assertEquals($expected, $pages->formatDate('2011-10-27T20:23:39'));
	}

}
