<?php
/**
 * @package		Endeleza
 * @subpackage	View
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * List view class
 *
 * @package		Endeleza
 * @subpackage	View
 */
class EViewList extends EView
{
	/**
	 * A list of items
	 *
	 * @var array
	 */
	public $items = null;

	/**
	 * A pagination object
	 *
	 * @var object
	 */
	public $pagination = null;

	/**
	 * A state object
	 *
	 * @var object
	 */
	public $state = null;

	/**
	 * Assign common grid data
	 *
	 * @return void
	 */
	protected function _assignData()
	{
		parent::_assignData();

		$items		= &$this->get('Items');
		$pagination = &$this->get('Pagination');
		$state		= &$this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('state',		$state);
	}

	/**
	 * Set toolbar buttons
	 *
	 * @return void
	 */
	protected function _setToolbar()
	{
		parent::_setToolbar();

		$this->_setSubmenu();
	}

	/**
	 * Sets administrator submenu using Submenu helper
	 *
	 * @return void
	 */
	protected function _setSubmenu()
	{
		// Load the helper class. with the name format BaseHelperSubmenu
		$this->loadHelper('submenu');

		$r = null;
		if (!preg_match('/(.*)View/i', get_class($this), $r)) {
			return;
		}

		$class = $r[1].'HelperSubmenu';
		if (class_exists($class)) {
			call_user_func(array($class, 'getSubmenu'), $this->getName());
		}
	}
}