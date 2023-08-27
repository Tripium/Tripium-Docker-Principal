<?php

namespace WilcityAdvancedProducts\Helpers;

use WilokeListingTools\Framework\Helpers\Validation;

class Cookie
{
    protected static function generateKeyPrefix($key)
    {
        return 'waw_'.$key;
    }
    
    public static function set($key, $val, $expired = 86400)
    {
        self::delete($key);
        setcookie(self::generateKeyPrefix($key), json_encode($val), time() + $expired, '/', false, is_ssl(), true);
    }
    
    public static function delete($key)
    {
        setcookie(self::generateKeyPrefix($key), null, time() - 86400, '/', false, is_ssl(), true);
    }
    
    public static function get($key, $default = false)
    {
        $key = self::generateKeyPrefix($key);
        if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
            if (Validation::isValidJson($_COOKIE[$key])) {
                $default = Validation::getJsonDecoded();
            }
        }
        
        return $default;
    }
}
