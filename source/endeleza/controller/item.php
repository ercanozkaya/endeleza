<?php
/**
 * @package		Endeleza
 * @subpackage	Controller
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Controller class to handle CRUD and other Joomla! related tasks
 *
 * @package		Endeleza
 * @subpackage	Controller
 */
class EControllerItem extends EController
{
	/**
	 * Default view name for grid
	 *
	 * @var string
	 */
	protected $_listView = null;

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('save2new',	'save');
		$this->registerTask('apply',	'save');

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('orderup',		'order');
		$this->registerTask('orderdown',	'order');
	}

	/**
	 * Set default names for views and models
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @return	void
	 */
	protected function _setDefaultNames($config = array())
	{
		parent::_setDefaultNames($config);

		$prefix = strtolower($this->getName());
		$suffix = strtolower($this->getNameSuffix());

		// Grid view name
		if (array_key_exists('list_view', $config)) {
			$this->_listView = $config['list_view'];
		}
		else {
			if (EInflector::isSingular($this->_defaultView)) {
				$this->_listView = EInflector::pluralize($this->_defaultView);
			}
			else {
				$this->_listView = $this->_defaultView;
			}
		}

		// Edit view name
		if (array_key_exists('item_view', $config)) {
			$this->_itemView = $config['item_view'];
		}
		else {
			$singularView = EInflector::singularize($this->_listView);

			if (!empty($singularView)) {
				$this->_itemView = $singularView;
			}
			else {
				if (!empty($suffix)) {
					$this->_itemView = $suffix;
				}
				else {
					$this->_itemView = $prefix;
				}
			}
		}
	}


	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(array('view' => $this->_listView));
	}

	/**
	 * Method to add a new item.
	 *
	 * @return	void
	 */
	public function add()
	{
		$this->setRedirect(array('view' => $this->_itemView));
	}

	/**
	 * Method to edit an existing item.
	 *
	 * @return	void
	 */
	public function edit()
	{
		$cid 	= JRequest::getVar('cid', array(0), '', 'array');
		$id		= JRequest::getVar('id', $cid[0], '', 'int');

		$this->setRedirect(array('view' => $this->_itemView, 'id' => $id));
	}


	/**
	 * Method to cancel an edit
	 *
	 * @return	void
	 */
	public function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('EController_Invalid_Token'));

		$this->setRedirect(array('view' => $this->_listView));
	}

	/**
	 * Method to save an item.
	 *
	 * @return	void
	 */
	public function save()
	{
		JRequest::checkToken() or jexit(JText::_('EController_Invalid_Token'));

		$model = $this->getModel();
		$data = JRequest::getVar('jform', array(), 'default', 'array');

		$return = $model->save($data);

		$msg = JText::_('EController_Item_Saved');
		$msgType = 'message';

		if ($return === false) {
			$msg = $model->getError();
			$msgType = 'error';
		}

		switch ($this->getTask()) {
			case 'save':
				$url = array('view' => $this->_listView);
				break;

			case 'save2new':
				$url = array('view' => $this->_itemView);
				break;

			case 'apply':
			default:
				$id = $model->getState('id');
				$return	= ($id ? $id : JRequest::getInt('id', 0));
				$url = array('view' => $this->_itemView, 'id' => $return);
				break;
		}

		$this->setRedirect($url, $msg, $msgType);
	}

	/**
	 * Method to delete item(s).
	 *
	 * @return	void
	 */
	public function delete()
	{
		JRequest::checkToken() or jexit(JText::_('EController_Invalid_Token'));

		$model = $this->getModel();

		$cid = JRequest::getVar('cid', array(), '', 'array');
		JArrayHelper::toInteger($cid);

		$return = $model->delete($cid);

		$msg = JText::_('EController_Items_Deleted');
		$msgType = 'message';

		if ($return === false) {
			$msg = $model->getError();
			$msgType = 'error';
		}

		$this->setRedirect(array('view' => $this->_listView), $msg, $msgType);
	}

	/**
	 * Method to publish/unpublish item(s).
	 *
	 * @return	void
	 */
	public function publish()
	{
		JRequest::checkToken() or jexit(JText::_('EController_Invalid_Token'));

		$model = $this->getModel();

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$task	= $this->getTask();
		$values	= array('publish' => 1, 'unpublish' => 0);
		$value	= JArrayHelper::getValue($values, $task, -1, 'int');

		$model->setStates($cid, $value);

		$this->setRedirect(array('view' => $this->_listView));
	}

	/**
	 * Method to change ordering of items.
	 *
	 * @return	void
	 */
	public function order()
	{
		JRequest::checkToken() or jexit(JText::_('EController_Invalid_Token'));

		$model = $this->getModel();

		$task	= $this->getTask();
		$values	= array('orderup' => -1, 'orderdown' => 1);
		$value	= JArrayHelper::getValue($values, $task, -1, 'int');

		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		$model->move((int) $cid[0], $value);

		$this->setRedirect(array('view' => $this->_listView));
	}

	/**
	 * Method to save the current ordering arrangement.
	 *
	 * @return	void
	 */
	public function saveorder()
	{
		JRequest::checkToken() or jexit(JText::_('EController_Invalid_Token'));

		$model = $this->getModel();

		// Get the input
		$cid	= JRequest::getVar('cid',	null,	'post',	'array');
		$order	= JRequest::getVar('order',	null,	'post',	'array');

		// Sanitize the input
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model->saveorder($cid, $order);

		$message = JText::_('EController_Ordering_Saved');
		$this->setRedirect(array('view' => $this->_listView), $message);
	}
}