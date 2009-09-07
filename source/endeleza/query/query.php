<?php
/**
 * @package		Endeleza
 * @subpackage	Database
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A query builder based on the one in Joomla! 1.6
 *
 * @package		Endeleza
 * @subpackage	Database
 */
class EQuery
{
	/** @var string The query type */
	protected $_type = '';
	/** @var object The select element */
	protected $_select = null;
	/** @var object The from element */
	protected $_from = null;
	/** @var object The join element */
	protected $_join = null;
	/** @var object The where element */
	protected $_where = null;
	/** @var object The where element */
	protected $_group = null;
	/** @var object The where element */
	protected $_having = null;
	/** @var object The where element */
	protected $_order = null;

	/**
	 * @param	mixed	A string or an array of field names
	 */
	public function select($columns)
	{
		$this->_type = 'select';
		if (is_null($this->_select)) {
			$this->_select = new EQueryElement('SELECT', $columns);
		} else {
			$this->_select->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names
	 */
	public function from($tables)
	{
		if (is_null($this->_from)) {
			$this->_from = new EQueryElement('FROM', $tables);
		} else {
			$this->_from->append($tables);
		}

		return $this;
	}

	/**
	 * @param	string
	 * @param	string
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->_join)) {
			$this->_join = array();
		}
		$this->_join[] = new EQueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	function &innerJoin($conditions)
	{
		$this->join('INNER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	function &outerJoin($conditions)
	{
		$this->join('OUTER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	function &leftJoin($conditions)
	{
		$this->join('LEFT', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	function &rightJoin($conditions)
	{
		$this->join('RIGHT', $conditions);

		return $this;
	}

	/**
	 * @param	mixed	A string or array of where conditions
	 * @param	string
	 */
	public function where($conditions, $glue='AND')
	{
		if (is_null($this->_where)) {
			$glue = strtoupper($glue);
			$this->_where = new EQueryElement('WHERE', $conditions, "\n\t$glue ");
		} else {
			$this->_where->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function group($columns)
	{
		if (is_null($this->_group)) {
			$this->_group = new EQueryElement('GROUP BY', $columns);
		} else {
			$this->_group->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function having($columns)
	{
		if (is_null($this->_having)) {
			$this->_having = new EQueryElement('HAVING', $columns);
		} else {
			$this->_having->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function order($columns)
	{
		if (is_null($this->_order)) {
			$this->_order = new EQueryElement('ORDER BY', $columns);
		} else {
			$this->_order->append($columns);
		}

		return $this;
	}

	/**
	 * @return	string	The completed query
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->_type)
		{
			case 'select':
				$query .= $this->_select->toString();
				$query .= $this->_from->toString();
				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= $join->toString();
					}
				}
				if ($this->_where) {
					$query .= $this->_where->toString();
				}
				if ($this->_group) {
					$query .= $this->_group->toString();
				}
				if ($this->_having) {
					$query .= $this->_having->toString();
				}
				if ($this->_order) {
					$query .= $this->_order->toString();
				}
				break;
		}

		return $query;
	}

	/**
	 * @return	string	The completed query
	 */
	public function toString()
	{
		return (string) $this;
	}

}


class EQueryElement
{
	/** @var string The name of the element */
	protected $_name = null;
	/** @var array An array of elements */
	protected $_elements = null;
	/** @var string Glue piece */
	protected $_glue = null;

	/**
	 * Constructor
	 * @param	string	The name of the element
	 * @param	mixed	String or array
	 * @param	string	The glue for elements
	 */
	public function __construct($name, $elements, $glue=',')
	{
		$this->_elements	= array();
		$this->_name		= $name;
		$this->append($elements);
		$this->_glue		= $glue;
	}

	/**
	 * Appends element parts to the internal list
	 * @param	mixed	String or array
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_unique(array_merge($this->_elements, $elements));
		} else {
			$this->_elements = array_unique(array_merge($this->_elements, array($elements)));
		}
	}

	/**
	 * Render the query element
	 * @return	string
	 */
	public function toString()
	{
		return "\n{$this->_name} " . implode($this->_glue, $this->_elements);
	}
}