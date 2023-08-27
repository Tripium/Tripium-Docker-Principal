<?php

namespace WilcityAdvancedSearch\Helpers;

use WilcityServiceClient\Helpers\GetSettings;

class App
{
	protected static $aRegistry;
	private static   $parentPluginPath       = 'wilcityservice-client/wilcityservice-client.php';
	private static   $parentMasterPluginPath = 'wilcityservice-client-master/wilcityservice-client.php';
	private static   $parentSetupOptionKey   = 'is_parent_setup';

	public static function bind($key, $value)
	{
		self::$aRegistry[$key] = $value;
	}

	public static function get($key)
	{
		if (array_key_exists($key, self::$aRegistry)) {
			return self::$aRegistry[$key];
		}

		return false;
	}

	private static function pluginName()
	{
		return basename(dirname(plugin_dir_path(__FILE__), 2));
	}

	public static function updateWSSetupOption($isError = false)
	{
		$key = self::pluginName();
		$aOptions = get_option(self::$parentSetupOptionKey);
		if (empty($aOptions)) {
			$aOptions = [];
		}

		if ($isError) {
			$aOptions[$key] = 1;
		} else {
			unset($aOptions[$key]);
		}

		update_option(self::$parentSetupOptionKey, $aOptions);
	}

	public static function isRequiredSetup()
	{
		$key = self::pluginName();
		$aOptions = get_option(self::$parentSetupOptionKey);

		return isset($aOptions[$key]) && $aOptions[$key] == 1;
	}

	public static function isWSSetup()
	{
		self::updateWSSetupOption();
		if (!defined('WILCITYSERIVCE_VERSION')) {
			self::updateWSSetupOption(true);

			return false;
		}

		$accessToken = GetSettings::getOptionField('secret_token');
		if (empty($accessToken)) {
			self::updateWSSetupOption(true);

			return false;
		}

		self::updateWSSetupOption(false);

		return true;
	}
}
