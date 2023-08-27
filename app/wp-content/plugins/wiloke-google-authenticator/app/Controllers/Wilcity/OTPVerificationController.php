<?php

namespace WilokeGoogleAuthenticator\Controllers\Wilcity;

use WilokeGoogleAuthenticator\Helpers\Cookie;
use WilokeGoogleAuthenticator\Helpers\GoogleAuthenticator;
use WilokeGoogleAuthenticator\Helpers\Session;
use WilokeListingTools\Framework\Helpers\SetSettings;

class OTPVerificationController
{
    private $oUser;
    
    public function __construct()
    {
        add_action(
          'wilcity/wiloke-listing-tools/app/Register/RegisterLoginController/handleLoginRegisterOnCustomLoginPage/wilcity_verify_otp',
          [$this, 'verifyOTP']
        );
        add_filter('wilcity/filter/wilcity-mobile-app/app/Controllers/LoginRegister/authentication',
          [$this, 'maybeVerifyOTP']);
        add_filter('wilcity/filter/wiloke-mobile-app/verify-otp', [$this, 'verifyMobileOTP'], 10, 2);
    }
    
    public function maybeVerifyOTP($aResponse)
    {
        if (!Session::isNeedToCheckOTP()) {
            return $aResponse;
        }
        
        $aResponse['next'] = 'verify-otp';
        SetSettings::deleteUserMeta($aResponse['oUserInfo']['userID'], 'app_token');
        unset($aResponse['token']);
        unset($aResponse['oUserInfo']);
        unset($aResponse['oUserNav']);
        
        return $aResponse;
    }
    
    private function beforeVerifyOTP($aData)
    {
        if (empty(Session::getCurrentUserId())) {
            Session::setSessionOTPError(
              esc_html__(
                'You need to log into the site with your account first',
                'wiloke-google-authenticator'
              )
            );
            
            return false;
        }
        
        if (!isset($aData['otp_code']) || empty($aData['otp_code'])) {
            Session::setSessionOTPError(esc_html__('Verification code is required', 'wiloke-google-authenticator'));
            
            return false;
        }
        
        $this->oUser = new \WP_User(Session::getCurrentUserId());
        if (empty($this->oUser) || is_wp_error($this->oUser)) {
            Session::setSessionOTPError(esc_html__('Invalid user id', 'wiloke-google-authenticator'));
            
            return false;
        }
        
        return true;
    }
    
    public function verifyMobileOTP($aResponse, $aData)
    {
        if (!$this->beforeVerifyOTP($aData)) {
            return $aResponse;
        }
        
        try {
            if (GoogleAuthenticator::verifyTwoFactorCode(trim($aData['otp_code']), Session::getCurrentUserId())) {
                Session::isNeedToCheckOTP(true);
                Session::removeSessionOTPError();
                
                return [
                  'status' => 'success',
                  'oUser'  => $this->oUser
                ];
            }
        } catch (\Exception $e) {
        }
    
        return $aResponse;
    }
    
    public function verifyOTP($aData)
    {
        if (!$this->beforeVerifyOTP($aData)) {
            return false;
        }
        
        try {
            if (GoogleAuthenticator::verifyTwoFactorCode(trim($aData['otp_code']), Session::getCurrentUserId())) {
                Session::isNeedToCheckOTP(true);
                Session::removeSessionOTPError();
                wp_set_current_user($this->oUser->ID, $this->oUser->user_login);
                wp_set_auth_cookie($this->oUser->ID);
                
                $redirectTo = Cookie::getCookieRedirectTo();
                $redirectTo = $redirectTo === 'self' || empty($redirectTo) ? home_url('/') : $redirectTo;
                wp_safe_redirect($redirectTo);
                exit();
            } else {
                Session::setSessionOTPError(esc_html__('Verification failed. Please try it again.',
                  'wiloke-google-authenticator'));
            }
        } catch (\Exception $e) {
            Session::setSessionOTPError($e->getMessage());
        }
    }
}
