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
	 * Overridden constructor
	 *
	 * @param	array	Configuration array
	 */
	public function __construct($config = array())
	{
		if (!empty($config['ignore_request'])) {
			$this->__state_set = true;
		}

		parent::__construct($config);
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
			// Private method to auto-populate the model state.
			$this->_populateState();

			// Set the model state set flat to true.
			$this->__state_set = true;
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