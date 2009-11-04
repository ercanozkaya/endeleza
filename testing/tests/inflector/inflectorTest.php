<?php

class EInflectorTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	}

	public function testPluralize()
	{
		$this->assertEquals('tests', EInflector::pluralize('test'));
	}

	public function testSingularize()
	{
		$this->assertEquals('test', EInflector::singularize('tests'));
	}

	public function testUncountable()
	{
		$this->assertEquals('aircraft', EInflector::singularize('aircraft'));
		$this->assertEquals('aircraft', EInflector::pluralize('aircraft'));
	}

	public function testIsPlural()
	{
		$this->assertTrue(EInflector::isSingular('test'));
		$this->assertTrue(EInflector::isPlural('tests'));
	}

	public function testOverride()
	{
		EInflector::addWord('test', 'different_plural');

		$this->assertEquals('test', EInflector::singularize('different_plural'));
		$this->assertEquals('different_plural', EInflector::pluralize('test'));
	}
}