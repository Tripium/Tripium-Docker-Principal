<?php

namespace WilokeGmailLogin\Helpers;

/**
 * Class Option
 * @package WilokeGmailLogin\Helpers
 */
class Option
{
	/**
	 * @var string
	 */
	private static $optionKey = 'wiloke-google';
	/**
	 * @var array
	 */
	private static $aGoogleApiOptions = [];

	public static function getAll()
	{
		self::$aGoogleApiOptions = get_option(self::$optionKey);
		$aDefaults = [
			'enable'        => 'no',
			'client_id'     => '',
			'client_secret' => '',
			'redirect_uri'  => ''
		];

		return !is_array(self::$aGoogleApiOptions) ? $aDefaults : wp_parse_args(self::$aGoogleApiOptions, $aDefaults);
	}

	/**
	 * @param $field
	 * @param string $default
	 * @return mixed|string
	 */
	public static function getField($field, $default = '')
	{
		self::getAll();

		return isset(self::$aGoogleApiOptions) ? self::$aGoogleApiOptions[$field] : $default;
	}

	/**
	 * @param $aValues
	 */
	public static function saveGoogleAPISettings($aValues)
	{
		update_option(self::$optionKey, $aValues);
	}
}
