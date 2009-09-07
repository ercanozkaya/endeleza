<?php
/**
 * @package		Endeleza
 * @subpackage	Model
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


/**
 * Item model that handles most of the basic operations on an item
 *
 * @package		Endeleza
 * @subpackage	Model
 */
class EModelItem extends EModel
{
	/**
	 * A database row.
	 *
	 * @var		object
	 */
	protected $_item = null;

	/**
	 * Method to get an object for a database row
	 *
	 * @param	int	ID of the row
	 * @return	object	A JObject instance
	 */
	public function getItem($id = null)
	{
		$id = (int) (!empty($id) ? $id : $this->getState('id'));

		$table = $this->getTable();

		if ($id > 0) {
			$return = $table->load($id);
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$value = JArrayHelper::toObject($table->getProperties(), 'JObject');
		return $value;
	}

	/**
	 * Method to save a row
	 *
	 * @param array	Data array
	 * @return bool|int False on failure. Item ID if successfull
	 */
	public function save($data)
	{
		if (empty($data['id'])) {
			$id = (int) $this->getState('id');
		}
		else {
			$id = (int) $data['id'];

			// This calls the _populateState method
			$this->getState();
		}
		$this->setState('id', $id);

		$isNew	= true;

		$table = $this->getTable();

		if ($id > 0) {
			$return = $table->load($id);
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}

			$isNew = false;
		}

		// Bind the data
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table, $isNew);

		// Check the data
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$this->setState('id', (int) $table->id);

		return $table->id;
	}

	/**
	 * Method to delete row(s)
	 * @param $cid	An array of IDs
	 * @return bool
	 */
	public function delete($cid)
	{
		$table = $this->getTable();

		foreach ($cid as $id) {
			$return = $table->load((int) $id);

			if ($return === false) {
				$this->setError($table->getError());
				return false;
			}

			$return = $table->delete();

			if ($return === false) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to set ordering of an object
	 *
	 * @param integer	Item Id
	 * @param integer -1 or +1
	 * @return bool
	 */
	public function move($id = null, $direction)
	{
		$table = &$this->getTable();

		if (empty($id)) {
			$id = $this->getState('id');
		}

		// Load the row.
		if (!$table->load($id)) {
			$this->setError($table->getError());
			return false;
		}

		// Move the row.
		$table->move($direction);

		return true;
	}

	/**
	 * Method to change ordering of items
	 *
	 * @param array	ID list
	 * @param array	Order values
	 * @param string	A field to group values
	 * @return bool
	 */
	public function saveorder($cid = array(), $order, $groupCol = null)
	{
		$table = $this->getTable();
		$groupings = array();

		// update ordering values
		for ($i=0; $i < count($cid); $i++) {
			// Load the row.
			if (!$table->load((int) $cid[$i])) {
				$this->setError($table->getError());
				return false;
			}

			if ($groupCol !== null) {
				$groupings[] = $table->{$groupCol};
			}

			if ($table->ordering != $order[$i]) {
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		if (count($groupings) == 0) {
			$table->reorder();
		} else {
			// execute updateOrder for each parent group
			$groupings = array_unique($groupings);
			foreach ($groupings as $group) {
				$table->reorder($groupCol.' = '.$group);
			}
		}

		return true;
	}

	/**
	 * Method to change states of items
	 *
	 * @param array	ID list
	 * @param integer New state value
	 * @return bool
	 */
	public function setStates($cid, $state = 0)
	{
		$table = $this->getTable();

		// Update the state for each row
		foreach ($cid as $id) {
			// Load the row.
			if (!$table->load((int) $id)) {
				$this->setError($table->getError());
				return false;
			}

			// Check the current ordering.
			if ($table->published != $state) {
				// Set the new ordering.
				$table->published = $state;

				// Save the row.
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to populate a table before saving
	 *
	 * @param object JTable instance to be saved
	 * @param bool	True of item to be saved is new
	 * @return void
	 */
	protected function _prepareTable(&$table, $isNew = false)
	{
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		parent::_populateState();

		$cid		= JRequest::getVar('cid', array(0), '', 'array');
		$id			= JRequest::getVar('id', $cid[0], '', 'int');
		$this->setState('id', $id);
	}
}