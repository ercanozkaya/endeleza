<?php
/**
 * @version		$Id: category.php 379 2009-06-17 07:16:53Z eddieajau $
 * @package		Catalog
 * @copyright	(C) 2008 - 2009 JXtended LLC. All rights reserved.
 * @license		GNU General Public License
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.field');


/**
 * List form field type object
 *
 * @package		JXtended.Libraries
 * @subpackage	Form
 * @since		1.0.11
 */
class JFormFieldCategories extends JFormField
{
   /**
	* Field type
	*
	* @access	protected
	* @var		string
	*/
	var	$_type = 'Categories';

	function _getOptions(&$node)
	{
		$db			= &JFactory::getDbo();
		$section	= $node->attributes('section');

		$query = new EQuery;

		$query->select('a.id AS value, a.title AS text')
			->from('#__categories AS a')
			->where('section = '.$db->quote($section))
			->order('a.ordering, a.title');

		$db->setQuery((string)$query);
		$options = $db->loadObjectList();

		if ($node->attributes('allow_none') == 'true') {
			array_unshift($options, JHtml::_('select.option', 0, '- '.JText::_('Select a Category').' -'));
		}

		return $options;
	}
}