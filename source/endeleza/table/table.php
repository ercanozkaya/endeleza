<?php
/**
 * @package		Endeleza
 * @subpackage	Table
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Table class
 *
 * @package		Endeleza
 * @subpackage	Table
 */
class ETable extends JTable
{
	/**
	 * Name of the table in the db schema relating to child class
	 *
	 * @var 	string
	 */
	public $_tbl = '';

	/**
	 * Name of the primary key field in the table
	 *
	 * @var		string
	 */
	public $_tbl_key = 'id';

	public function __construct(&$db)
	{
		if (empty($this->_tbl)) {
			$this->_tbl = $this->_findTableName();
		}
		$this->_db = &$db;
	}

	/**
	 * Finds the database table name for this class
	 *
	 * @return string table name
	 */
	protected function _findTableName()
	{
		list($prefix, $suffix) = explode('Table', get_class($this), 2);

		if (empty($suffix)) {
			throw new Exception('Invalid Table Name: '.get_class($this));
		}

		$name = '#__'.(!empty($prefix) ? $prefix.'_' : '').EInflector::pluralize($suffix);

		return strtolower($name);
	}

	/**
	 * Sets primary key
	 *
	 * @param string name of the primary key field in the table
	 * @return void
	 */
	public function setKeyName($key)
	{
		$this->_tbl_key = $key;
	}

	/**
	 * Sets table name
	 *
	 * @param string name of the database table
	 * @return void
	 */
	public function setTableName($table)
	{
		$this->_tbl = $table;
	}
}