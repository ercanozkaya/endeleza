<?php
/**
 * @package		Endeleza
 * @subpackage	View
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Import Joomla! dependency
 */
jimport('joomla.application.component.view');

/**
 * Base view class
 *
 * @package		Endeleza
 * @subpackage	View
 */
class EView extends JView
{
	/**
	 * Display a template after assigning data
	 *
	 * @param string	An optional template name
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->_assignData();
		$this->_setToolbar();

		parent::display($tpl);
	}

	/**
	 * Assign view data
	 *
	 * @return void
	 */
	protected function _assignData()
	{
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