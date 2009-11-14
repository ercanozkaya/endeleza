<?php
/**
 * @version		$Id: category.php 13486 2009-11-13 00:35:57Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Supports an HTML select list of categories
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldCategory extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Category';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db			= &JFactory::getDbo();
		$section	= $this->_element->attributes('section');

		$query = new EQuery;

		$query->select('a.id AS value, a.title AS text')
			->from('#__categories AS a')
			->where('section = '.$db->quote($section))
			->order('a.ordering, a.title');

		$db->setQuery((string)$query);
		$options = $db->loadObjectList();

		if ($this->_element->attributes('allow_none') == 'true') {
			array_unshift($options, JHtml::_('select.option', 0, '- '.JText::_('Select a Category').' -'));
		}

		return $options;
	}
}