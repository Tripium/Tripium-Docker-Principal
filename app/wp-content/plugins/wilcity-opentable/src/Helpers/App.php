<?php

namespace WilcityOpenTable\Helpers;

use WilcityServiceClient\Helpers\GetSettings;

class App
{
    private static $aRegistry;
    private static $parentPluginPath = 'wilcityservice-client/wilcityservice-client.php';
    private static $parentSetupOptionKey = 'is_parent_setup';

    public static function bind($key, $val)
    {
        self::$aRegistry[$key] = $val;
    }

    public static function get($key)
    {
        return isset(self::$aRegistry[$key]) ? self::$aRegistry[$key] : false;
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
