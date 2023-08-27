<?php

namespace WooKit\Dashboard\Shared;

class Option
{
	private static string $optionKey = 'wookit_auth';

	private static array $aDataOptions = [];

	public static function saveAuthSettings(array $aValues)
	{
		update_option(self::$optionKey, $aValues);
	}

	public static function deleteAuthSettings()
	{
		delete_option(self::$optionKey);
	}

	public static function getUsername()
	{
		return self::getAuthField('username', '');
	}

	public static function getAuthField($field, $default = '')
	{
		self::getAuthSettings();
		return self::$aDataOptions[$field] ?? $default;
	}

	public static function getAuthSettings()
	{
		self::$aDataOptions = get_option(self::$optionKey) ?: [];
		if (empty(self::$aDataOptions)) {
			self::$aDataOptions = ['username' => '', 'app_password' => '', 'uuid' => ''];
		}
		return self::$aDataOptions;
	}

	public static function getApplicationPassword()
	{
		return self::getAuthField('app_password', '');
	}
}
