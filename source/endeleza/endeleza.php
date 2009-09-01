<?php
/**
 * Base class for Endeleza libraries
 *
 * Including this file is enough to bootstrap the framework
 *
 * @package		Endeleza
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Endeleza is loaded
 */
if (!defined('ENDELEZA')) {
	define('ENDELEZA', 1);
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

// Register our autoloader
spl_autoload_register(array('Endeleza', 'load'));

// Register __autoload function if exists
if (function_exists('__autoload')) {
	spl_autoload_register('__autoload');
}

/**
 * Holds version information and autoloader for classes
 *
 * @package		Endeleza
 */
class Endeleza
{
	/**
	 * Library version
	 */
	const VERSION = '0.1.0';

	/**
	 * Base path of the library
	 *
	 * @var	string
	 */
    protected static $_path;

	/**
	 * Returns library version
	 *
	 * @return	string	Library version
	 */
	public static function getVersion()
	{
		return self::VERSION;
	}

	/**
	 * Returns library path
	 *
	 * @return	string	Library path
	 */
    public static function getPath()
    {
    	if(!isset(self::$_path)) {
        	self::$_path = dirname(__FILE__);
        }

        return self::$_path;
    }

	/**
	 * Autoloader for Endeleza library.
	 *
	 * @param	string	Class name to find
	 *
	 * @return	boolean	True if the class is an Endeleza class and loaded. False otherwise.
	 */
	public static function load($class)
	{
		if (substr($class, 0, 1) == 'E') {
			$class = substr($class, 1);
			$spacified = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', ' \1', $class));
			$parts = explode(' ', $spacified);

			if (count($parts) > 1) {
				$path = implode(DS, $parts);
			} else {
				$path = $spacified.DS.$spacified;
			}

			$base = self::getPath();

			$file = $base.DS.$path.'.php';
			if (is_file($file)) {
				include $file;
				return true;
			} else {
				return false;
			}
		}

		return false;
	}
}