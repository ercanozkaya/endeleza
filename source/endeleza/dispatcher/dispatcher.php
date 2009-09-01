<?php
/**
 * @package		Endeleza
 * @subpackage	Dispatcher
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Dispatcher for component entry point files
 *
 * @package		Endeleza
 * @subpackage	Dispatcher
 */
class EDispatcher
{
	/**
	 * Dispatch the component
	 *
	 * @param $config array An optional config array for controller
	 * @return void
	 */
	public static function dispatch($config = array())
	{
		$prefix = empty($config['prefix']) ? null : $config['prefix'];
		unset($config['prefix']);

		try {
			$controller = EController::getInstance($prefix, $config);
			$controller->execute(JRequest::getCmd('task'));
			$controller->redirect();
		} catch (Exception $e) {
			JError::raiseError($e->getCode(), $e->getMessage());
		}
	}
}