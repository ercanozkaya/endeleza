<?php
/**
 * @version		$Id: hidden.php 12774 2009-09-18 04:47:09Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.field');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldHidden extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Hidden';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$class	= $this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : '';

		return '<input type="hidden" name="'.$this->inputName.'" value="'.htmlspecialchars($this->value).'" '.$class.' />';
	}
}