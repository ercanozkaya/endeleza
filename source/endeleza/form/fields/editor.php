<?php
/**
 * @version		$Id: editor.php 379 2009-06-17 07:16:53Z eddieajau $
 * @package		JXtended.Libraries
 * @subpackage	Form
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link		http://jxtended.com
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.editor');
jximport('jxtended.form.field');

/**
 * JXtended Form Field Type Class for an Editor.
 *
 * @package		JXtended.Libraries
 * @subpackage	Form
 * @version		1.0
 */
class JFormFieldEditor extends JFormField
{
   /**
	* Field type
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Editor';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		// editor attribute can be in the form of:
		// editor="desired|alternative"
		if ($editorName = trim($this->_element->attributes('editor')))
		{
			$parts	= explode('|', $editorName);
			$db		= &JFactory::getDbo();
			$query	= 'SELECT element' .
					' FROM #__extensions' .
					' WHERE element	= '.$db->Quote($parts[0]) .
					'  AND folder = '.$db->Quote('editors') .
					'  AND enabled = 1';
			$db->setQuery($query);
			if ($db->loadResult()) {
				$editorName	= $parts[0];
			}
			else if (isset($parts[1])) {
				$editorName	= $parts[1];
			}
			else {
				$editorName	= '';
			}
			$this->_element->addAttribute('editor', $editorName);
		}
		$editor		= &JFactory::getEditor($editorName ? $editorName : null);
		$rows		= $this->_element->attributes('rows');
		$cols		= $this->_element->attributes('cols');
		$height		= ($this->_element->attributes('height')) ? $this->_element->attributes('height') : '250';
		$width		= ($this->_element->attributes('width')) ? $this->_element->attributes('width') : '100%';
		$class		= ($this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="text_area"');
		$buttons	= $this->_element->attributes('buttons');

		if ($buttons == 'true') {
			$buttons	= true;
		} else {
			$buttons	= explode(',', $buttons);
		}
		// convert <br /> tags so they are not visible when editing
		//$value	= str_replace('<br />', "\n", $value);

		return $editor->display($this->inputName, htmlspecialchars($this->value), $width, $height, $cols, $rows, $buttons);
	}

	function render(&$xmlElement, $value, $controlName = 'jxform')
	{
		$result		= &parent::render($xmlElement, $value, $controlName);
		$editorName	= trim($xmlElement->attributes('editor'));
		$result->editor	= &JFactory::getEditor($editorName ? $editorName : null);
		return $result;
	}
}