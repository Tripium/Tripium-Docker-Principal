<?php


namespace WilcityVR\Helpers;


class App
{
	private static $aRepository = [];

	public static function bind($key, $val)
	{
		self::$aRepository[$key] = $val;
	}

	public static function get($key, $default = '')
	{
		return array_key_exists($key, self::$aRepository) ? self::$aRepository[$key] : $default;
	}
}