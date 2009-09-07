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
	public $_tbl		= '';

	/**
	 * Name of the primary key field in the table
	 *
	 * @var		string
	 */
	public $_tbl_key	= 'id';
}