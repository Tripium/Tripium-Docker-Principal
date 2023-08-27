<?php

namespace WilokeGoogleAuthenticator\Helpers;

class Cookie
{
    public static function getCookie($key, $thenDestroy = false)
    {
        $val = array_key_exists($key, $_COOKIE) ? $_COOKIE[$key] : false;
        
        if ($thenDestroy) {
            self::destroyCookie($key);
        }
        
        return $val;
    }
    
    public static function destroyCookie($key)
    {
        self::setCookie($key, '', time() - 1000);
        unset($_COOKIE[$key]);
    }
    
    public static function setCookie(
      $name,
      $val,
      $expiration = null,
      $path = '/',
      $domain = null,
      $secure = null,
      $httpOnly = true
    ) {
        $secure = $secure ? $secure : is_ssl();
        setcookie($name, $val, $expiration, $path, $domain, $secure, $httpOnly);
    }
    
    public static function setCookieRedirectTo($redirectTo)
    {
        self::destroyCookie('wga-redirect-to');
        self::setCookie('wga-redirect-to', $redirectTo);
    }
    
    public static function getCookieRedirectTo($thenDestroy = false)
    {
        return self::getCookie('wga-redirect-to', $thenDestroy);
    }
}
