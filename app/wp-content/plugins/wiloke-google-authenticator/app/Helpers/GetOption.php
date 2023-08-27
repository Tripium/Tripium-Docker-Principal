<?php

namespace WilokeGoogleAuthenticator\Helpers;

class GetOption
{
    public static function getOptionDetail($field = null, $default = '')
    {
        if (class_exists('\WilokeThemeOptions')) {
            return \WilokeThemeOptions::getOptionDetail($field, $default);
        }
        
        return false;
    }
    
    public static function isEnable($field = null)
    {
        if (class_exists('\WilokeThemeOptions')) {
            return \WilokeThemeOptions::isEnable($field);
        }
        
        return false;
    }
    
    public static function isEnableGTA()
    {
        return self::isEnable('wga_toggle');
    }
}
