<?php
namespace WilokeGoogleAuthenticator\Helpers;

class Session
{
    /**
     * @param null $sessionID
     */
    public static function sessionStart($sessionID = null)
    {
        global $pagenow;
        if ($pagenow == 'site-health.php' || (is_admin() && isset($_GET['page']) && $_GET['page'] == 'site-health')) {
            session_id($sessionID);
        }
        
        if (!headers_sent() && (session_status() == PHP_SESSION_NONE || session_status() === 1)) {
            session_start();
        }
    }
    
    /**
     * @param $key
     * @param $val
     */
    public static function setSession($key, $val)
    {
        self::sessionStart();
        $_SESSION[$key] = $val;
    }
    
    /**
     * @param $key
     */
    public static function destroySession($key)
    {
        self::sessionStart();
        if (!empty($key)) {
            unset($_SESSION[$key]);
        } else {
            session_destroy();
        }
    }
    
    public static function getSession($key, $thenDestroy = false)
    {
        self::sessionStart();
        $val = is_array($_SESSION) && array_key_exists($key, $_SESSION) ? $_SESSION[$key] : false;
        
        if ($thenDestroy) {
            self::destroySession($key);
        }
        
        return $val;
    }
    
    public static function setNeedToCheckOTP()
    {
        self::setSession('wiloke-need-to-check-otp', 'yes');
    }
    
    public static function isNeedToCheckOTP($thenDestroy = false)
    {
        $status = self::getSession('wiloke-need-to-check-otp', $thenDestroy);
        
        return $status === 'yes';
    }
    
    public static function getCurrentUserId($thenDestroy = false)
    {
        return self::getSession('wiloke-current-user-id', $thenDestroy);
    }
    
    public static function setCurrentUserId($userId = null)
    {
        $userId = empty($userId) ? get_current_user_id() : $userId;
        self::setSession('wiloke-current-user-id', $userId);
    }
    
    public static function setSessionOTPError($msg)
    {
        self::setSession('wiloke-opt-error', $msg);
    }
    
    public static function getSessionOTPError($thenDestroy = false)
    {
        return self::getSession('wiloke-opt-error', $thenDestroy);
    }
    
    public static function removeSessionOTPError()
    {
        self::destroySession('wiloke-opt-error');
    }
}
