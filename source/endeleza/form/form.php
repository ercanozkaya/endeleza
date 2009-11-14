<?php
/**
 * @package		Endeleza
 * @subpackage	Form
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Form Class for the Joomla Framework.
 * Taken from Joomla! 1.6
 *
 * @package		Endeleza
 * @subpackage	Form
 * @version		1.6
 */
class EForm extends JObject
{
	/**
	 * The form name.
	 *
	 * @since	1.6
	 * @var		string
	 */
	protected $_name;

	/**
	 * An array of options for form groups.
	 *
	 * @since	1.6
	 * @var		array
	 */
	protected $_fieldsets	= array();

	/**
	 * Form groups containing field objects.
	 *
	 * @since	1.6
	 * @var		array
	 */
	protected $_groups		= array();

	/**
	 * Form data containing field values.
	 *
	 * @since	1.6
	 * @var		array
	 */
	protected $_data		= array();

	/**
	 * Form options.
	 *
	 * @since	1.6
	 * @var		array
	 */
	protected $_options		= array();

	/**
	 * Method to get an instance of a form.
	 *
	 * @param	string		$name		The name of the form.
	 * @param	string		$data		The name of an XML file or an XML string.
	 * @param	string		$file		Flag to toggle whether the $data is a file path or a string.
	 * @param	array		$options	An array of options to pass to the form.
	 * @return	object		A JForm instance.
	 * @since	1.6
	 */
	public static function &getInstance($data, $name = 'form', $file = true, $options = array())
	{
		static $instances;

		if ($instances == null) {
			$instances = array();
		}

		// Only load the form once.
		if (!isset($instances[$name]))
		{
			// Instantiate the form.
			$instances[$name] = new EForm($options);

			// Set the form name.
			$instances[$name]->setName($name);

			// Load the data.
			if ($file) {
				$instances[$name]->load($data, true, true);
			} else {
				$instances[$name]->load($data, false, true);
			}

		}

		return $instances[$name];
	}

	/**
	 * Method to construct the object on instantiation.
	 *
	 * @param	array		$options	An array of form options.
	 * @return	void
	 * @since	1.6
	 */
	public function __construct($options = array())
	{
		// Set the options if specified.
		$this->_options['array'] = array_key_exists('array', $options) ? $options['array'] : false;
	}

	/**
	 * Method to get the form name.
	 *
	 * @return	string		The name of the form.
	 * @since	1.6
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Method to set the form name.
	 *
	 * @param	string		$name	The new name of the form.
	 * @return	void
	 * @since	1.6
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * Method to recursively bind values to form fields.
	 *
	 * @param	mixed		$data	An array or object of form values.
	 * @param	string		$group	The group to bind the fields to.
	 * @return	boolean		True on success, false otherwise.
	 * @since	1.6
	 */
	public function bind($data, $limit = null)
	{
		// The data must be an object or array.
		if (!is_object($data) && !is_array($data)) {
			return false;
		}

		// Convert objects to arrays.
		if (is_object($data))
		{
			// Handle a JRegistry/JParameter object.
			if ($data instanceof JRegistry) {
				$data = $data->toArray();
			}
			// Handle a JObject.
			elseif ($data instanceof JObject) {
				$data = $data->getProperties();
			}
			// Handle other types of objects.
			else {
				$data = (array)$data;
			}
		}

		// Iterate through the groups.
		foreach ($this->_groups as $group => $fields)
		{
			// Bind if no group is specified or if the group matches the current group.
			if ($limit === null || ($limit !== null && $group === $limit))
			{
				// Iterate through the values.
				foreach ($data as $k => $v)
				{
					// If the field name matches the name of the group and the value is not scalar, recurse.
					if ($k == $group && !is_scalar($v) && !is_resource($v)) {
						$this->bind($v, $group);
					}
					else {
						// Bind the value to the field if it exists.
						if (isset($this->_groups[$group][$k]) && is_object($this->_groups[$group][$k])) {
							$this->_data[$group][$k] = $v;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to load the form description from a file or string.
	 *
	 * If $data is a file name, $file must be set to true. The reset option
	 * works on a group basis. If the XML file or string references groups
	 * that have already been created they will be replaced with the fields
	 * in the new file unless the $reset parameter has been set to false.
	 *
	 * @param	string		$data		The name of an XML file or an XML string.
	 * @param	string		$file		Flag to toggle whether the $data is a file path or a string.
	 * @param	string		$reset		Flag to toggle whether the form description should be reset.
	 * @return	boolean		True on success, false otherwise.
	 * @since	1.6
	 */
	public function load($data, $file = true, $reset = true)
	{
		$return = false;

		// Make sure we have data.
		if (!empty($data))
		{
			// Get the XML parser and load the data.
			$parser	= &JFactory::getXMLParser('Simple');

			// If the data is a file, load the XML from the file.
			if ($file)
			{
				// If we were not given the absolute path of a form file, attempt to find one.
				if (!is_file($data)) {
					jimport('joomla.filesystem.path');
					$data = JPath::find(EForm::addFormPath(), strtolower($data).'.xml');
				}

				// Attempt to load the XML file.
				$loaded = $parser->loadFile($data);
			}
			// Load the data as a string.
			else {
				$loaded	= $parser->loadString($data);
			}
			// Make sure the XML was loaded.
			if ($loaded)
			{
				// Check if any groups exist.
				if (isset($parser->document->fields))
				{
					// Load the form groups.
					foreach ($parser->document->fields as $group)
					{
						$this->loadFieldsXML($group, $reset);
						$return = true;
					}
				}

				// Check if a name is set.
				if ($parser->document->attributes('name') && $reset) {
					$this->setName($parser->document->attributes('name'));
				}
			}
		}

		return $return;
	}

	/**
	 * Method to load form descriptions from a complete folder.
	 *
	 * This function loads all files that have the via $name defined prefix
	 * in the folders defined by addIncludePath()
	 *
	 * @param	string		$name		The prefix-name of the XML files
	 * @return	boolean		True on success, false otherwise.
	 * @since	1.6
	 */
	public function loadFolder($name)
	{
		$return = false;

		jimport('joomla.filesystem.folder');
		$files = JFolder::files($this->addFormPath(), $name, false, true);
		if (count($files))
		{
			foreach($files as $file)
			{
				$result = $this->load($file, true, true);
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	/**
	 * Method to recursively filter data for form fields.
	 *
	 * @param	array		$data		The data to filter.
	 * @param	string		$limit		An optional group to limit the filtering to.
	 * @return	array		An array of filtered data.
	 * @since	1.6
	 */
	public function filter($data, $limit = null)
	{
		// Initialize variables.
		$return = array();

		// The data must be an object or array.
		if (!is_object($data) && !is_array($data)) {
			return false;
		}

		// Get some system objects.
		$config	= JFactory::getConfig();
		$user	= JFactory::getUser();
		// Convert objects to arrays.
		if (is_object($data))
		{
			// Handle a JRegistry/JParameter object.
			if ($data instanceof JRegistry) {
				$data = $data->toArray();
			}
			// Handle a JObject.
			elseif ($data instanceof JObject) {
				$data = $data->getProperties();
			}
			// Handle other types of objects.
			else {
				$data = (array)$data;
			}
		}

		// Static input filters for specific settings.
		static $noHtmlFilter;
		static $safeHtmlFilter;

		// Get the safe HTML filter if not set.
		if (is_null($safeHtmlFilter)) {
			$safeHtmlFilter = &JFilterInput::getInstance(null, null, 1, 1);
		}

		// Get the no HTML filter if not set.
		if (is_null($noHtmlFilter)) {
			$noHtmlFilter = &JFilterInput::getInstance(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
		}

		// Iterate through the groups.
		foreach ($this->_groups as $group => $fields)
		{
			// Filter if no group is specified or if the group matches the current group.
			if ($limit === null || ($limit !== null && $group === $limit))
			{
				// If the group name matches the name of a group in the data and the value is not scalar, recurse.
				if (isset($data[$group]) && !is_scalar($data[$group]) && !is_resource($data[$group]))
				{
					$return[$group] = $this->filter($data[$group], $group);
				}
				else
				{
					// Filter the fields.
					foreach ($fields as $name => $field)
					{
						// Get the field information.
						$filter	= $field->attributes('filter');

						// Check for a value to filter.
						if (isset($data[$name]))
						{
							// Handle the different filter options.
							switch (strtoupper($filter))
							{
								case 'RULES':
									$return[$name] = array();
									foreach ((array) $data[$name] as $action => $ids)
									{
										// Build the rules array.
										$return[$name][$action] = array();
										foreach ($ids as $id => $p)
										{
											if ($p !== '') {
												$return[$name][$action][$id] = ($p == '1' || $p == 'true') ? true : false;
											}
										}
									}
									break;
								case 'UNSET':
									// Do nothing.
									break;

								case 'RAW':
									// No Filter.
									$return[$name] = $data[$name];
									break;

								case 'SAFEHTML':
									// Filter safe HTML.
									$return[$name] = $safeHtmlFilter->clean($data[$name], $filter);
									break;

								case 'SERVER_UTC':
									// Convert a date to UTC based on the server timezone offset.
									if (intval($data[$name]))
									{
										$offset	= $config->getValue('config.offset');

										$date	= JFactory::getDate($data[$name], $offset);
										$return[$name] = $date->toMySQL();
									}
									break;

								case 'USER_UTC':
									// Convert a date to UTC based on the user timezone offset.
									if (intval($data[$name]))
									{
										$offset	= $user->getParam('timezone', $config->getValue('config.offset'));

										$date   = JFactory::getDate($data[$name], $offset);
										$return[$name] = $date->toMySQL();
									}
									break;

								default:
									if (strpos($filter, '::') !== false && is_callable(explode('::', $filter)))
									{
										// Filter using the callback method.
										$return[$name] = call_user_func(explode('::', $filter), $data[$name]);
									}
									else if (function_exists($filter))
									{
										// Filter using the callback function.
										$return[$name] = call_user_func($filter, $data[$name]);
									}
									else {
										// Filter using JFilterInput. All HTML code is filtered by default.
										$return[$name] = $noHtmlFilter->clean($data[$name], $filter);
									}
									break;

							}
						}
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Method to validate form data.
	 *
	 * Validation warnings will be pushed into JForm::_errors and should be
	 * retrieved with JForm::getErrors() when validate returns boolean false.
	 *
	 * @param	array		$data		An array of field values to validate.
	 * @param	string		$limit		An option group to limit the validation to.
	 * @return	mixed		Boolean on success, JException on error.
	 * @since	1.6
	 */
	public function validate($data, $limit = null)
	{
		$return = true;
		$data	= (array)$data;

		// Check if the group exists.
		if ($limit !== null && !isset($this->_groups[$limit])) {
			// The group that was supposed to be filtered does not exist.
			return new JException(JText::sprintf('LIBRARIES FORM VALIDATOR GROUP NOT FOUND', $limit), 0, E_ERROR);
		}

		// Get a validator object.
		$validator = new EFormValidator();

		// Iterate through the groups.
		foreach ($this->_groups as $group => $fields)
		{
			// Filter if no group is specified or if the group matches the current group.
			if ($limit === null || ($limit !== null && $group === $limit))
			{
				// If the group name matches the name of a group in the data and the value is not scalar, pass the group.
				if (isset($data[$group]) && !is_scalar($data[$group]) && !is_resource($data[$group])) {
					$results = $validator->validate($this->_groups[$group], $data[$group]);
				} else {
					// Run the validator over the group.
					$results = $validator->validate($this->_groups[$group], $data);
				}

				// Check for a error.
				if (JError::isError($results)) {
					return new JException($results->getMessage(), 0, E_ERROR);
				}

				// Check the validation results.
				if (count($results))
				{
					// Get the validation messages.
					foreach ($results as $result) {
						if (JError::isError($result) && $result->get('level') === E_WARNING) {
							$this->setError($result);
							$return = false;
						}
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Method to add a field to a group.
	 *
	 * @param	object		$field		The field object to add.
	 * @param	string		$group		The group to add the field to.
	 * @return	void
	 * @since	1.6
	 */
	public function addField(&$field, $group = '_default')
	{
		// Add the field to the group.
		$this->_groups[$group][$field->attributes('name')] = &$field;
	}

	/**
	 * Method to add an array of fields to a group.
	 *
	 * @param	array		$fields	An array of field objects to add.
	 * @param	string		$group	The group to add the fields to.
	 * @return	void
	 * @since	1.6
	 */
	public function addFields(&$fields, $group = '_default')
	{
		// Add the fields to the group.
		foreach ($fields as $field) {
			$this->_groups[$group][$field->attributes('name')] = $field;
		}
	}

	/**
	 * Method to get a form field.
	 *
	 * @param	string		$name			The name of the form field.
	 * @param	string		$group			The group the field is in.
	 * @param	mixed		$formControl	The optional form control. Set to false to disable.
	 * @param	mixed		$groupControl	The optional group control. Set to false to disable.
	 * @param	mixed		$value			The optional value to render as the default for the field.
	 * @return	object		Rendered Form Field object
	 * @since	1.6
	 */
	public function getField($name, $group = '_default', $formControl = '_default', $groupControl = '_default', $value = null)
	{
		// Get the XML node.
		$node = isset($this->_groups[$group][$name]) ? $this->_groups[$group][$name] : null;

		// If there is no XML node for the given field name, return false.
		if (empty($node)) {
			return false;
		}

		// Load the field type.
		$type	= $node->attributes('type');
		$field	= & $this->loadFieldType($type);

		// If the field could not be loaded, get a text field.
		if ($field === false) {
			$field = & $this->loadFieldType('text');
		}

		// Get the value for the form field.
		if ($value === null) {
			$value = (array_key_exists($name, $this->_data[$group]) && ($this->_data[$group][$name] !== null)) ? $this->_data[$group][$name] : $node->attributes('default');
		}

		// Check the form control.
		if ($formControl == '_default' && $this->_options['array'] == false) {
			$formControl = false;
		} elseif ($formControl == '_default' && $this->_options['array'] == true) {
			$formControl = $this->_options['array'];
		}

		// Check the group control.
		if ($groupControl == '_default' && $this->_fieldsets[$group]['array'] == false) {
			$groupControl = false;
		} elseif ($groupControl == '_default' && $group !== '_default' && $this->_fieldsets[$group]['array'] == true) {
			$groupControl = $group;
		} elseif ($groupControl == '_default' && $group !== '_default' && is_string($this->_fieldsets[$group]['array']) && strlen($this->_fieldsets[$group]['array'])) {
			$groupControl = $this->_fieldsets[$group]['array'];
		}

		// Render the field.
		return $field->render($node, $value, $formControl, $groupControl);
	}

	/**
	 * Method to get a field attribute value.
	 *
	 * @param	string		$field			The name of the field.
	 * @param	string		$attribute		The name of the attribute.
	 * @param	mixed		$default		The default value of the attribute.
	 * @param	string		$group			The optional group of the field.
	 * @return	mixed		The value of the attribute if set, otherwise the default value.
	 * @since	1.6
	 */
	public function getFieldAttribute($field, $attribute, $default, $group = '_default')
	{
		$return = null;

		// Get the field attribute if it exists.
		if (isset($this->_groups[$group][$field])) {
			$return = $this->_groups[$group][$field]->attributes($attribute);
		}

		return $return !== null ? $return : $default;
	}

	/**
	 * Method to replace a field in a group.
	 *
	 * @param	object		$field		The field object to replace.
	 * @param	string		$group		The group to replace the field in.
	 * @return	boolean		True on success, false when field does not exist.
	 * @since	1.6
	 */
	public function setField(&$field, $group = '_default')
	{
		$return = false;

		// Add the fields to the group if it exists.
		if (isset($this->_groups[$group][$field->attributes('name')])) {
			$this->_groups[$group][$field->attributes('name')] = $field;
			$return = true;
		}

		return $return;
	}

	/**
	 * Method to remove a field from a group.
	 *
	 * @param	string		$field		The field to remove.
	 * @param	string		$group		The group to remove.
	 * @return	void
	 * @since	1.6
	 */
	public function removeField($field, $group = '_default')
	{
		unset($this->_groups[$group][$field]);
	}

	/**
	 * Method to set a field attribute value.
	 *
	 * @param	string		$field			The name of the field.
	 * @param	string		$attribute		The name of the attribute.
	 * @param	mixed		$value			The value to set the attribute to.
	 * @param	string		$group			The optional group of the field.
	 * @return	boolean		True on success, false when field does not exist.
	 * @since	1.6
	 */
	public function setFieldAttribute($field, $attribute, $value, $group = '_default')
	{
		$return = false;

		// Set the field attribute if it exists.
		if (isset($this->_groups[$group][$field])) {
			$this->_groups[$group][$field]->addAttribute($attribute, $value);
			$return = true;
		}

		return $return;
	}

	/**
	 * Method to get the fields in a group.
	 *
	 * @param	string		$group			The form field group.
	 * @param	mixed		$formControl	The optional form control. Set to false to disable.
	 * @param	mixed		$groupControl	The optional group control. Set to false to disable.
	 * @return	array		Associative array of rendered Form Field object by field name
	 * @since	1.6
	 */
	public function getFields($group = '_default', $formControl = '_default', $groupControl = '_default')
	{
		$results = array();

		// Check the form control.
		if ($formControl == '_default' && $this->_options['array'] == false) {
			$formControl = false;
		} elseif ($formControl == '_default' && $this->_options['array'] == true) {
			$formControl = $this->_options['array'];
		}

		// Check the group control.
		if ($groupControl == '_default' && (empty($this->_fieldsets[$group]) || $this->_fieldsets[$group]['array'] == false)) {
			$groupControl = false;
		} elseif ($groupControl == '_default' && $group !== '_default' && isset($this->_fieldsets[$group]) && $this->_fieldsets[$group]['array'] == true) {
			$groupControl = $group;
		} elseif ($groupControl == '_default' && $group !== '_default' && isset($this->_fieldsets[$group]) && is_string($this->_fieldsets[$group]['array']) && strlen($this->_fieldsets[$group]['array'])) {
			$groupControl = $this->_fieldsets[$group]['array'];
		}

		// Check if the group exists.
		if (isset($this->_groups[$group]))
		{
			// Get the fields in the group.
			foreach ($this->_groups[$group] as $name => $node)
			{
				// Get the field info.
				$type	= $node->attributes('type');
				$value	= (isset($this->_data[$group]) && array_key_exists($name, $this->_data[$group]) && ($this->_data[$group][$name] !== null)) ? $this->_data[$group][$name] : $node->attributes('default');

				// Load the field.
				$field = &$this->loadFieldType($type);

				// If the field could not be loaded, get a text field.
				if ($field === false) {
					$field = &$this->loadFieldType('text');
				}

				// Render the field.
				$results[$name] = $field->render($node, $value, $formControl, $groupControl);
			}
		}

		return $results;
	}

	/**
	 * Method to assign an array of fields to a group.
	 *
	 * @param	array		$fields		An array of field objects to assign.
	 * @param	string		$group		The group to assign the fields to.
	 * @return	void
	 * @since	1.6
	 */
	public function setFields(&$fields, $group = '_default')
	{
		// Reset the fields group,
		$this->_groups[$group] = array();

		// Add the fields to the group.
		foreach ($fields as $field) {
			$this->_groups[$group][$field->attributes('name')] = $field;
		}
	}

	public function getFieldsets()
	{
		return $this->_fieldsets;
	}

	/**
	 * Method to get a list of groups.
	 *
	 * @return	array	An array of groups.
	 * @since	1.6
	 */
	public function getGroups()
	{
		return array_keys($this->_groups);
	}

	/**
	 * Method to remove a group.
	 *
	 * @param	string		$group		The group to remove.
	 * @return	void
	 * @since	1.6
	 */
	public function removeGroup($group)
	{
		unset($this->_groups[$group]);
	}

	/**
	 * Method to get the input control for a field.
	 *
	 * @param	string		$name			The field name.
	 * @param	string		$group			The group the field is in.
	 * @param	mixed		$formControl	The optional form control. Set to false to disable.
	 * @param	mixed		$groupControl	The optional group control. Set to false to disable.
	 * @param	mixed		$value			The optional value to render as the default for the field.
	 * @return	string		The form field input control.
	 * @since	1.6
	 */
	public function getInput($name, $group = '_default', $formControl = '_default', $groupControl = '_default', $value = null)
	{
		// Render the field input.
		$field = $this->getField($name, $group, $formControl, $groupControl, $value);
		$input = $field->input;
		return $input;
	}

	/**
	 * Method to get the label for a field.
	 *
	 * @param	string		$name			The field name.
	 * @param	string		$group			The group the field is in.
	 * @param	mixed		$formControl	The optional form control. Set to false to disable.
	 * @param	mixed		$groupControl	The optional group control. Set to false to disable.
	 * @param	mixed		$value			The optional value to render as the default for the field.
	 * @return	string		The form field label.
	 * @since	1.6
	 */
	public function getLabel($name, $group = '_default', $formControl = '_default', $groupControl = '_default', $value = null)
	{
		// Render the field label.
		$field = $this->getField($name, $group, $formControl, $groupControl, $value);
		$label = $field->label;
		return $label;
	}

	/**
	 * Method to get the value of a field.
	 *
	 * @param	string		$field		The field to set.
	 * @param	mixed		$default	The default value of the field if empty.
	 * @param	string		$group		The group the field is in.
	 * @return	boolean		The value of the field or the default value if empty.
	 * @since	1.6
	 */
	public function getValue($field, $default = null, $group = '_default')
	{
		$return = null;

		// Get the field value if it exists.
		if (isset($this->_groups[$group][$field]) && is_object($this->_groups[$group][$field])) {
			$return = array_key_exists($field, $this->_data[$group]) ? $this->_data[$group][$field] : $default;
		}

		return $return;
	}

	/**
	 * Method to set the value of a field.
	 *
	 * @param	string		$field		The field to set.
	 * @param	mixed		$value		The value to set the field to.
	 * @param	string		$group		The group the field is in.
	 * @return	boolean		True if field exists, false otherwise.
	 * @since	1.6
	 */
	public function setValue($field, $value, $group = '_default')
	{
		// Set the field if it exists.
		if (isset($this->_groups[$group][$field]) && is_object($this->_groups[$group][$field])) {
			$this->_data[$group][$field] = $value;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Loads form fields from an XML fields element optionally reseting fields before loading new ones.
	 *
	 * @param	object		$xml		The XML fields object.
	 * @param	boolean		$reset		Flag to toggle whether the form groups should be reset.
	 * @return	boolean		True on success, false otherwise.
	 * @since	1.6
	 */
	public function loadFieldsXML(&$xml, $reset = true)
	{
		// Check for an XML object.
		if (!is_object($xml)) {
			return false;
		}

		// Get the group name.
		$group = ($xml->attributes('group')) ? $xml->attributes('group') : '_default';

		if ($reset) {
			// Reset the field group.
			$this->setFields($xml->children(), $group);
		} else {
			// Add to the field group.
			$this->addFields($xml->children(), $group);
		}

		// Initialize the data group.
		if ($reset) {
			$this->_data[$group] = array();
		} else {
			if (!isset($this->_data[$group])) {
				$this->_data[$group] = array();
			}
		}

		// Get the fieldset attributes.
		$this->_fieldsets[$group] = array();

		// Get the fieldset label.
		if ($value = $xml->attributes('label')) {
			$this->_fieldsets[$group]['label'] = $value;
		}

		// Get the fieldset description.
		if ($value = $xml->attributes('description')) {
			$this->_fieldsets[$group]['description'] = $value;
		}

		// Get an optional hidden setting (at the discretion of the renderer to honour).
		if ($value = $xml->attributes('hidden')) {
			$this->_fieldsets[$group]['hidden'] = ($value == 'true' || $value == 1) ? true : false;
		}

		// Get the fieldset array option.
		switch ($xml->attributes('array'))
		{
			case 'true':
				$this->_fieldsets[$group]['array'] = true;
				break;

			case 'false':
			case '':
				$this->_fieldsets[$group]['array'] = false;
				break;

			default:
				$this->_fieldsets[$group]['array'] = $xml->attributes('array');
				break;
		}

		// Check if there is a field path to handle.
		if ($xml->attributes('addfieldpath'))
		{
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.path');
			$path = JPath::clean(JPATH_ROOT.DS.$xml->attributes('addfieldpath'));

			// Add the field path to the list if it exists.
			if (JFolder::exists($path)) {
				EForm::addFieldPath($path);
			}
		}

		return true;
	}

	/**
	 * Method to load a form field object.
	 *
	 * @param	string		$type		The field type.
	 * @param	boolean		$new		Flag to toggle whether we should get a new instance of the object.
	 * @return	mixed		Field object on success, false otherwise.
	 * @since	1.6
	 */
	public function &loadFieldType($type, $new = true)
	{
		$false	= false;
		$key	= md5($type);
		$class	= 'JFormField'.ucfirst($type);

		// Return the field object if it already exists and we don't need a new one.
		if (isset($this->_fieldTypes[$key]) && $new === false) {
			return $this->_fieldTypes[$key];
		}

		if (!class_exists('JFormFieldList')) {
			require_once dirname(__FILE__).DS.'fields'.DS.'list.php';
		}
		if (!class_exists('JFormFieldText')) {
			require_once dirname(__FILE__).DS.'fields'.DS.'text.php';
		}

		if (!class_exists($class))
		{
			$paths = EForm::addFieldPath();

			// If the type is complex, add the base type to the paths.
			if ($pos = strpos($type, '_'))
			{
				// Add the complex type prefix to the paths.
				for ($i = 0, $n = count($paths); $i < $n; $i++)
				{
					// Derive the new path.
					$path = $paths[$i].DS.strtolower(substr($type, 0, $pos));

					// If the path does not exist, add it.
					if (!in_array($path, $paths)) {
						array_unshift($paths, $path);
					}
				}

				// Break off the end of the complex type.
				$type = substr($type, $pos+1);
			}

			// Try to find the field file.
			jimport('joomla.filesystem.path');
			if ($file = JPath::find($paths, strtolower($type).'.php')) {
				require_once $file;
			} else {
				return $false;
			}

			// Check once and for all if the class exists.
			if (!class_exists($class)) {
				return $false;
			}
		}

		// Instantiate a new field object.
		$this->_fieldTypes[$key] = new $class($this);

		return $this->_fieldTypes[$key];
	}

	/**
	 * Method to add a path to the list of form include paths.
	 *
	 * @param	mixed		$new		A path or array of paths to add.
	 * @return	array		The list of paths that have been added.
	 * @since	1.6
	 * @static
	 */
	public static function addFormPath($new = null)
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array(dirname(__FILE__).DS.'forms');
		}

		// Force path to an array.
		settype($new, 'array');

		// Add the new paths to the list if not already there.
		foreach ($new as $path) {
			if (!in_array($path, $paths)) {
				array_unshift($paths, trim($path));
			}
		}

		return $paths;
	}

	/**
	 * Method to add a path to the list of field include paths.
	 *
	 * @param	mixed		$new		A path or array of paths to add.
	 * @return	array		The list of paths that have been added.
	 * @since	1.6
	 * @static
	 */
	public static function addFieldPath($new = null)
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array(dirname(__FILE__).DS.'fields');
		}

		// Force path to an array.
		settype($new, 'array');

		// Add the new paths to the list if not already there.
		foreach ($new as $path) {
			if (!in_array($path, $paths)) {
				array_unshift($paths, trim($path));
			}
		}

		return $paths;
	}
}

/*
 * Dummy classes for 1.6 compatibility
 */
class JFormRule extends EFormRule
{
}

abstract class JFormField extends EFormField
{
	/**
	 * Method to get the field input.
	 *
	 * @access	protected
	 * @return	string		The field input.
	 * @since	1.6
	 */
	abstract protected function _getInput();
}