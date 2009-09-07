<?php
/**
 * @package		Endeleza
 * @subpackage	View
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Item view class
 *
 * @package		Endeleza
 * @subpackage	View
 */
class EViewItem extends EView
{
	/**
	 * An item row
	 *
	 * @var object
	 */
	public $item = null;

	/**
	 * A state object
	 *
	 * @var object
	 */
	public $state = null;

	/**
	 * Assign common form data
	 *
	 * @return void
	 */
	protected function _assignData()
	{
		$item		= $this->get('Item');
		$state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('item',	$item);
		$this->assignRef('state',	$state);
	}

	/**
	 * Set toolbar buttons
	 *
	 * @return void
	 */
	protected function _setToolbar()
	{
	}
}