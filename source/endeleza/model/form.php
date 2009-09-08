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

class EModelForm extends EModelItem
{
	/**
	 * Array of form objects.
	 */
	protected $_forms = array();

	/**
	 * Overloaded save method to validate the data first
	 *
	 * @param array	Data array
	 * @return bool|int False on failure. Item ID if successfull
	 */
	public function save($data)
	{
		$form = $this->getForm();
		if ($form === false) {
			return false;
		}

		$data = $this->validate($form, $data);

		if ($data === false) {
			return false;
		}

		return parent::save($data);
	}

	/**
	 * Method to get a form object.
	 *
	 * @param	string		$xml		The form data. Can be XML string if file flag is set to false.
	 * @param	array		$options	Optional array of parameters.
	 * @param	boolean		$clear		Optional argument to force load a new form.
	 * @return	mixed		JForm object on success, False on error.
	 */
	public function &getForm($xml = null, $name = 'form', $options = array(), $clear = false)
	{
		if ($xml === null) {
			$xml = strtolower($this->getName());
		}

		// Handle the optional arguments.
		$options['array']	= array_key_exists('array',	$options) ? $options['array'] : 'jform';
		$options['file']	= array_key_exists('file',	$options) ? $options['file']  : true;
		$options['event']	= array_key_exists('event',	$options) ? $options['event'] : null;
		$options['group']	= array_key_exists('group',	$options) ? $options['group'] : null;

		// Create a signature hash.
		$hash = md5($xml.serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		// Get the form.
		EForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'forms');
		EForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'fields');
		EFormValidator::addRulePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'rules');

		EForm::addFormPath(JPATH_COMPONENT_SITE.DS.'models'.DS.'forms');
		EForm::addFieldPath(JPATH_COMPONENT_SITE.DS.'models'.DS.'fields');
		EFormValidator::addRulePath(JPATH_COMPONENT_SITE.DS.'models'.DS.'rules');

		$form = &EForm::getInstance($xml, $name, $options['file'], $options);

		// Check for an error.
		if (JError::isError($form))
		{
			$this->setError($form->getMessage());
			$false = false;
			return $form;
		}

		// Look for an event to fire.
		if ($options['event'] !== null)
		{
			// Get the dispatcher.
			$dispatcher	= &JDispatcher::getInstance();

			// Load an optional plugin group.
			if ($options['group'] !== null) {
				JPluginHelper::importPlugin($options['group']);
			}

			// Trigger the form preparation event.
			$results = $dispatcher->trigger($options['event'], array($form->getName(), $form));

			// Check for errors encountered while preparing the form.
			if (count($results) && in_array(false, $results, true))
			{
				// Get the last error.
				$error = $dispatcher->getError();

				// Convert to a JException if necessary.
				if (!JError::isError($error)) {
					$error = new JException($error, 500);
				}

				return $error;
			}
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @access	public
	 * @param	object		$form		The form to validate against.
	 * @param	array		$data		The data to validate.
	 * @return	mixed		Array of filtered data if valid, false otherwise.
	 * @since	1.1
	 */
	public function validate($form, $data)
	{
		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}