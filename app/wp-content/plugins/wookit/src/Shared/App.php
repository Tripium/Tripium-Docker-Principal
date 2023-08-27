<?php


namespace WooKit\Shared;


class App
{
    private static array $aRegistry;

    public static function bind($key, $value)
    {
        self::$aRegistry[$key] = $value;
    }

    public static function get($key)
    {
        return array_key_exists($key, self::$aRegistry) ? self::$aRegistry[$key] : '';
    }
}
