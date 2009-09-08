<?php

class EInflectorTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		EInflector::addWord('singular', 'plural');
	}

	public function testGetPlural()
	{
		$this->assertEquals('singular', EInflector::getSingular('plural'));
	}

	public function testGetSingular()
	{
		$this->assertEquals('plural', EInflector::getPlural('singular'));
	}

	public function testOverride()
	{
		EInflector::addWord('singular', 'test');

		$this->assertEquals('singular', EInflector::getSingular('test'));
		$this->assertEquals('test', EInflector::getPlural('singular'));
	}

	public function testIsPlural()
	{
		$this->assertTrue(EInflector::isSingular('singular'));
		$this->assertTrue(EInflector::isPlural('plural'));

		$this->assertFalse(EInflector::isSingular('random'));
		$this->assertFalse(EInflector::isPlural('random'));
	}

	public function testInvalidValues()
	{
		$this->assertEquals(null, EInflector::getSingular('randomword'));
		$this->assertEquals(null, EInflector::getPlural('randomword'));
	}
}