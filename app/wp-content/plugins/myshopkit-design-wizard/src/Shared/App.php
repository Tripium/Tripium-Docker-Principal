<?php

namespace MyshopKitDesignWizard\Shared;

/**
 * Class App
 * @package HSBlogCore\Helpers
 */
class App
{
	private static $aRegistry = [];

	/**
	 * @param $key
	 * @param $val
	 */
	public static function bind($key, $val)
	{
		self::$aRegistry[$key] = $val;
	}

	/**
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get($key)
	{
		return isset(self::$aRegistry[$key]) ? self::$aRegistry[$key] : false;
	}

	public static function bindConfig($file)
	{
		self::bind('configs/' . $file, include PROOMOLAND_DIR . 'configs/' . $file . '.php');
	}

	public static function getConfig($file)
	{
		return self::get('configs/' . $file);
	}
}
