<?php
/**
 * @package		Endeleza
 * @subpackage	Controller
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Import Joomla! dependency
 */
jimport('joomla.application.component.controller');

/**
 * Base controller to handle common tasks
 *
 * @package		Endeleza
 * @subpackage	Controller
 */
class EController extends JController
{
	/**
	 * The name suffix of the controller
	 *
	 * @var	string
	 */
	protected $_suffix = null;

	/**
	 * Default view name
	 *
	 * @var string
	 */
	protected $_defaultView = null;


	/**
	 * Default model name
	 *
	 * @var string
	 */
	protected $_defaultModel = null;

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// we should remove helper methods from task list
		$eMethods	= get_class_methods('EController');
		$jMethods	= get_class_methods('JController');
		$methods	= array_diff($eMethods, $jMethods);

		foreach ($methods as $method) {
			$method = strtolower($method);
			unset($this->_taskMap[$method]);
		}

		if (array_key_exists('suffix', $config)) {
			$this->_suffix = $config['suffix'];
		}
		else {
			$this->_suffix = $this->getNameSuffix();
		}

		$this->_setDefaultNames($config);
	}

	/**
	 * Set default names for views and models
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @return	void
	 */
	protected function _setDefaultNames($config = array())
	{
		$prefix = strtolower($this->getName());
		$suffix = strtolower($this->getNameSuffix());

		// Default view name
		if (array_key_exists('default_view', $config)) {
			$this->_defaultView = $config['default_view'];
		}
		else {
			if (empty($suffix)) {
				// use the prefix
				$this->_defaultView = $prefix;
			}
			else {
				// Use suffix if it's plural, otherwise plurizalize it
				if (EInflector::isSingular($suffix)) {
					$this->_defaultView = EInflector::pluralize($suffix);
				}
				else {
					$this->_defaultView = $suffix;
				}
			}
		}

		// Default model name
		if (array_key_exists('default_model', $config)) {
			$this->_defaultModel = $config['default_model'];
		}
		else {
			// Default model names are always singular
			$this->_defaultModel = empty($suffix) ? $prefix : $suffix;
		}
	}

	/**
	 * Typical view method for MVC based architecture
	 *
	 * @param	string	If true, the view output will be cached
	 */
	public function display($cachable = false)
	{
		$document =& JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd('view', $this->_defaultView);
		$viewLayout	= JRequest::getCmd('layout', 'default');

		$view = &$this->getView($viewName, $viewType, '', array('base_path'=>$this->_basePath));

		// Get/Create the model
		if ($model = &$this->getModel($viewName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		// Assing a JDocument instance to our view
		$view->assignRef('document', $document);

		// Display the view
		if ($cachable && $viewType != 'feed') {
			$option = JRequest::getCmd('option');
			$cache =& JFactory::getCache($option, 'view');
			$cache->get($view, 'display');
		} else {
			$view->display();
		}
	}

	/**
	 * Overloaded function to override name
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	object	The model.
	 */
	public function &getModel($name = '', $prefix = '', $config = array())
	{
		if (empty($name)) {
			$name = $this->_defaultModel;
		}

		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param	mixed  A string for URL to redirect to or a named array.
	 * @param	string	Message to display on redirect. Optional, defaults to
	 * 			value set internally by controller, if any.
	 * @param	string	Message type. Optional, defaults to 'message'.
	 * @param	bool	If true, URL will be run through JRoute::_
	 * @return	void
	 */
	public function setRedirect($url = null, $msg = null, $type = 'message', $route = true)
	{
		if (is_array($url)) {
			// convert to string
			$url = $this->_buildURLFromArray($url);
		}
		elseif ($url === null) {
			$url = 'index.php?option=com_'.strtolower($this->getName());
		}

		if ($route === true) {
			$url = JRoute::_($url, false);
		}

		parent::setRedirect($url, $msg, $type);
	}

	/**
	 * Converts a named array to URL for redirection
	 *
	 * @param  array Named array
	 * @return string Redirect URL
	 */
	protected function _buildURLFromArray(&$url)
	{
		$str = '';
		$str .= 'index.php?option=';

		// add component name
		$str .= !empty($url['option']) ? $url['option'] : 'com_'.strtolower($this->getName());
		unset($url['option']);

		// add task to the url
		if (isset($url['task'])) {
			$str .= '&task='.$url['task'];
			unset($url['task']);
		}

		// add specified view to the url if set, otherwise use default view
		if (isset($url['view'])) {
			$str .= '&view='.(!empty($url['view']) ? $url['view'] : $this->_listView);
			unset($url['view']);
		}

		foreach ($url as $key => $value) {
			$str .= '&'.$key.'='.$value;
		}

		return $str;
	}

	/**
	 * Method to get the controller name suffix
	 *
	 * By default, it is found by parsing class name, or it can be set
	 * by passing a $config['suffix'] in the class constructor
	 *
	 * @return	string The name suffix of the controller
	 */
	public function getNameSuffix()
	{
		if (empty($this->_suffix)) {
			$matches = null;
			$result = null;

			if (preg_match('/Controller(.*)$/i', get_class($this), $matches)) {
				$this->_suffix = $matches[1];
			}
		}

		return $this->_suffix;
	}

	/**
	 * Method to get a singleton controller instance.
	 *
	 * @param	string	The prefix for the controller.
	 * @param	array	An array of optional constructor options.
	 * @return	object	Controller class or an EController instance on error as a fallback.
	 */
	public static function &getInstance($prefix = null, $config = array())
	{
		$path 		= isset($config['base_path']) ? $config['base_path'] : JPATH_COMPONENT;
		$protocol 	= JRequest::getWord('protocol');
		$task 		= JRequest::getCmd('task');

		// use component name if a prefix is not supplied
		$prefix 	= !empty($prefix) ? $prefix : str_replace('com_', '', JRequest::getCmd('option'));
		$suffix		= '';

		if (strpos($task, '.') !== false) {
			// we have a defined controller/task pair. split them out and reset the task
			$pieces = explode('.', $task);
			$suffix = $pieces[0];
			JRequest::setVar('task', $pieces[1]);
		}

		$file 	= self::_createFileName('controller', array('name' => $suffix, 'protocol' => $protocol));
		$path 	.= '/'.(!empty($suffix) ? 'controllers/' : '').$file;

		// Get the controller class name.
		$className = ucfirst($prefix).'Controller'.ucfirst($suffix);

		// Include the class if not present.
		if (!class_exists($className) && file_exists($path)) {
			require_once $path;
		}

		// Instantiate the class.
		if (!class_exists($className)) {
			// Fallback to EController or EControllerItem
			$config['name'] = ucfirst($prefix);
			$className = 'EController';
			if (!empty($suffix)) {
				$config['suffix'] = ucfirst($suffix);
				$className .= 'Item';
			}
		}

		$instance = new $className($config);
		return $instance;
	}

	/**
	 * Create the filename for a resource.
	 * Overloaded to add controller file support
	 *
	 * @param	string	The resource type to create the filename for.
	 * @param	array	An associative array of filename information. Optional.
	 * @return	string	The filename.
	 */
	public function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type) {
			case 'controller':
				if (empty($parts['name'])) {
					$parts['name'] = 'controller';
				}
				if (!empty($parts['protocol'])) {
					$parts['protocol'] = '.'.$parts['protocol'];
				}

				$filename = strtolower($parts['name']).$parts['protocol'].'.php';
				break;
			case 'view':
			default:
				$filename = parent::_createFileName($type, $parts);
			break;
		}
		return $filename;
	}
}