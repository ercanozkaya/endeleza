<?php
/**
 * @package		Endeleza
 * @subpackage	Inflector
 * @copyright	Copyright (C) 2009 Ercan Ozkaya. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to hold plural/singular names for words.
 *
 * Inspired by Nooku Framework class KInflector. (http://www.nooku.org/en/framework.html)
 *
 * @package		Endeleza
 * @subpackage	Inflector
 */
class EInflector
{
	protected static $_words = array(
		'singular' => array(),
		'plural'   => array()
	);

	/**
	 * Add a word to the cache, useful to make exceptions or to add words in
	 * other languages
	 *
	 * @param	string	Singular word
	 * @param 	string	Plural word
	 */
	public static function addWord($singular, $plural)
	{
		self::$_words['plural'][$singular]	= $plural;
		self::$_words['singular'][$plural] 	= $singular;
	}

	public static function getPlural($singular)
	{
		if (isset(self::$_words['plural'][$singular])) {
			return self::$_words['plural'][$singular];
		}

		return null;
	}

	public static function getSingular($plural)
	{
		if (isset(self::$_words['singular'][$plural])) {
			return self::$_words['singular'][$plural];
		}

		return null;
	}

	public static function isPlural($word)
	{
		return array_key_exists($word, self::$_words['singular']);
	}

	public static function isSingular($word)
	{
		return array_key_exists($word, self::$_words['plural']);
	}
}