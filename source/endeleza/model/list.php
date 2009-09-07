<?php
/**
 * @package		Endeleza
 * @subpackage	Model
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


/**
 * List model that handles most of the basic operations for a grid
 *
 * @package		Endeleza
 * @subpackage	Model
 */
class EModelList extends EModel
{
	/**
	 * An array of items.
	 *
	 * @var		array
	 */
	protected $_items = null;

	/**
	 * Total row count
	 *
	 * @var		integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var		object
	 */
	protected $_pagination = null;

	/**
	 * Method to get a list of items.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		if (empty($this->_items)) {
			$query = $this->_getListQuery();

			$this->_items = $this->_getList($query, (int)$this->getState('list.start'), (int)$this->getState('list.limit'));

			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return $this->_items;
	}

	/**
	 * Method to get the total number of items.
	 *
	 * @return	int		The number of items.
	 */
	public function getTotal()
	{
		if (empty($this->_total)) {
			$query = $this->_getListQuery(true);
			$this->_total = $this->_getListCount($query);

			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object.
	 *
	 * @return	object	A JPagination object.
	 */

	public function getPagination()
	{
		jimport('joomla.html.pagination');

		return new JPagination($this->getTotal(), (int)$this->getState('list.start'), (int)$this->getState('list.limit'));
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string		An SQL query
	 */
	protected function _getListQuery($count = false)
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

		// Initialize variables.
		$app		= JFactory::getApplication();
		$context	= $this->_context.'.';

		// Load the filter state.
		$published = $app->getUserStateFromRequest($context.'filter.published', 'published', '*', 'string');
		$this->setState('filter.state', ($published == '*' ? null : $published));
		$this->setState('filter.search', $app->getUserStateFromRequest($context.'filter.search', 'search', ''));

		// Load the list state.
		$this->setState('list.start', $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int'));
		$this->setState('list.limit', $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit'), 'int'));

		$this->setState('list.ordering', $app->getUserStateFromRequest($context.'list.ordering', 'filter_order', 'a.ordering', 'cmd'));
		$this->setState('list.direction', $app->getUserStateFromRequest($context.'list.direction', 'filter_order_Dir', 'ASC', 'word'));
	}
}