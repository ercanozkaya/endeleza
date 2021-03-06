<?php
/**
 * @package		Endeleza
 * @subpackage	Model
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Import Joomla! dependency
 */
jimport('joomla.application.component.model');

/**
 * Base model that adds easier state handling features to JModel
 *
 * @package		Endeleza
 * @subpackage	Model
 */
class EModel extends JModel
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'group.type';

	/**
	 * @var	boolean	Has the state been autoset yet
	 */
	protected $__state_set = false;

	/**
	 * @var string Class name prefix
	 */
	protected $_prefix = null;

	/**
	 * Overridden constructor
	 *
	 * @param	array	Configuration array
	 */
	public function __construct($config = array())
	{
		if (!empty($config['ignore_request'])) {
			$this->__state_set = true;
		}

		$this->_prefix = isset($config['prefix']) ? $config['prefix'] : $this->getNamePrefix();

		parent::__construct($config);
	}

	public function getTable($name = '', $prefix = '', $options = array())
	{
		if (empty($name)) {
			$name = $this->getName();
		}

		if (empty($prefix)) {
			$prefix = $this->getNamePrefix().'Table';
		}

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the model name prefix
	 *
	 * By default, it is found by parsing class name, or it can be set
	 * by passing a $config['prefix'] in the class constructor
	 *
	 * @return	string The name prefix of the model
	 */
	public function getNamePrefix()
	{
		if (empty($this->_prefix)) {
			$matches = null;

			if (preg_match('/(.*)Model/i', get_class($this), $matches)) {
				$this->_prefix = $matches[1];
			}
		}

		return $this->_prefix;
	}

	/**
	 * Method to get model state variables
	 *
	 * @param	string	Optional parameter name
	 * @param   mixed	Optional default value
	 * @return	object	The property where specified, the state object where omitted
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->__state_set) {
			// Set the model state set flat to true.
			$this->__state_set = true;

			// Private method to auto-populate the model state.
			$this->_populateState();

		}

		return $property === null ? $this->_state : $this->_state->get($property, $default);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
	}

	/**
	 * Returns an object list
	 *
	 * @param	string The query
	 * @param	int Offset
	 * @param	int The number of records
	 * @return	array
	 */
	public function &_getList($query, $limitstart = 0, $limit = 0)
	{
		$query = (string) $query;

		$result = parent::_getList($query, $limitstart, $limit);

		return $result;
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param	string The query
	 * @return	int
	 */
	public function _getListCount($query)
	{
		$query = (string) $query;

		return parent::_getListCount($query);
	}
}