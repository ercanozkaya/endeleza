<?php
/**
 * @package		Endeleza
 * @subpackage	Controller
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
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
	 * Method to get a singleton controller instance.
	 *
	 * @param	string	The prefix for the controller.
	 * @param	array	An array of optional constructor options.
	 * @return	object	Controller class or an EController instance on error as a fallback.
	 */
	public static function &getInstance($prefix = null, $config = array())
	{
		// Get the environment configuration.
		$basePath	= array_key_exists('base_path', $config) ? $config['base_path'] : JPATH_COMPONENT;
		$protocol	= JRequest::getWord('protocol');
		$command	= JRequest::getCmd('task', null);

		// Use component name if a prefix is not supplied
		$prefix = !empty($prefix) ? $prefix : str_replace('com_', '', JRequest::getCmd('option'));

		if (strpos($command, '.') !== false) {
			// We have a defined controller/task pair -- lets split them out
			list($controller, $task) = explode('.', $command);
			$controller	= strtolower($controller);

			// Define the controller filename and path.
			$file	= self::_createFileName('controller', array('name' => $controller, 'protocol' => $protocol));
			$path	= $basePath.DS.'controllers'.DS.$file;

			// Reset the task without the contoller context.
			JRequest::setVar('task', $task);
		} else {
			// Base controller.
			$controller	= '';
			$task	= $command;

			// Define the controller filename and path.
			$file	= self::_createFileName('controller', array('name' => 'controller', 'protocol' => $protocol));
			$path	= $basePath.DS.$file;
		}

		// Get the controller class name.
		$class = ucfirst($prefix).'Controller'.ucfirst($controller);

		// Include the class if not present.
		if (!class_exists($class)) {
			if (file_exists($path)) {
				require_once $path;
			}
		}

		// Instantiate the class.
		if (class_exists($class)) {
			$instance = new $class($config);
		} else {
			// Fallback to EController
			$config['name'] = ucfirst($prefix);
			$instance = new EController($config);
		}

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