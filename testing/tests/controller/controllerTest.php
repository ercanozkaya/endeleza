<?php

class EControllerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		// Set up minimalist Joomla framework
		if (! defined('_JEXEC')) {
			define('_JEXEC', 1);
		}

		// include other dependancies
		//jimport('joomla.database.query');
	}

	public function testRedirect()
	{
		$this->markTestIncomplete();
	}
}