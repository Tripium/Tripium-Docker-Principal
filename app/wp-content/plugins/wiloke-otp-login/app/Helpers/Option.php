<?php

namespace WilokeOTPLogin\Helpers;

/**
 * Class Option
 * @package WilokeOTPLogin\Helpers
 */
class Option
{
	/**
	 * @var string
	 */
	private static $optionKey = 'wilokeotp';
	/**
	 * @var array
	 */
	private static $aOTPLoginOptions = [];

	/**
	 * @return array|mixed|void
	 */
	public static function getOTPLoginSettings()
	{
		self::$aOTPLoginOptions = get_option(self::$optionKey);
		if (empty(self::$aOTPLoginOptions)) {
			self::$aOTPLoginOptions = ['is_enable' => 'no', 'expiration_time' => 1, 'email_subject' => 'OTP Code', 'email_content' => 'Your OTP Code is %OTPcode%'];
		}

		return self::$aOTPLoginOptions;
	}

	public static function getOTPField($field, $default = '')
	{
		self::getOTPLoginSettings();
		return isset(self::$aOTPLoginOptions[$field]) ? self::$aOTPLoginOptions[$field] : $default;
	}

	/**
	 * @param $aValues
	 */
	public static function saveOTPLoginSettings($aValues)
	{
		update_option(self::$optionKey, $aValues);
	}

	public static function isEnable()
	{
		return self::getOTPField('is_enable', 'no');
	}

	public static function getExpirationTime()
	{
		return self::getOTPField('expiration_time', 0);
	}
}
