<?php

namespace WilokeInstagramFeedhub\Helpers;

class App
{
	protected static $aRegistry = [];

	public static function get($key)
	{
		return array_key_exists($key, self::$aRegistry) ? self::$aRegistry[$key] : '';
	}

	public static function bind($key, $val)
	{
		self::$aRegistry[$key] = $val;
		return $val;
	}
}
