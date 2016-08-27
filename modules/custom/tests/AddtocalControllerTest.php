<?php

require_once('../addtocal/src/Controller/AddtocalController.php');

class AddtocalControllerTest extends PHPUnit_Framework_TestCase {

	$pages = new AddtocalController();

	public function testFormatDate() {
		$expected = '20111027T202339Z';

		$this->assertEquals($expected, $pages->formatDate('2011-10-27T20:23:39'));
	}

}
