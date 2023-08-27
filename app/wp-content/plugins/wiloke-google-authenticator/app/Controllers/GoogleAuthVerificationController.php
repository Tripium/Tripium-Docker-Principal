<?php

namespace WilokeGoogleAuthenticator\Controllers;

use WilokeGoogleAuthenticator\Helpers\Cookie;
use WilokeGoogleAuthenticator\Helpers\GetOption;
use WilokeGoogleAuthenticator\Helpers\GoogleAuthenticator;
use WilokeGoogleAuthenticator\Helpers\Session;
use WilokeGoogleAuthenticator\Helpers\User;

class GoogleAuthVerificationController
{
    public function __construct()
    {
        add_filter('authenticate', [$this, 'checkOtp'], 999);
        add_action('wiloke-google-authenticator/check-otp', [$this, 'checkOtp'], 999);
        add_filter('login_redirect', [$this, 'modifyLoginRedirection'], 99);
        add_action('wp_ajax_nopriv_wiloke_google_auth_verify_otp', [$this, 'verifyOTPCode']);
    }
    
    public function verifyOTPCode()
    {
        if (Session::isNeedToCheckOTP()) {
            $userId = Session::getCurrentUserId();
            
            if (empty($userId)) {
                wp_send_json_error([
                  'msg' => esc_html__('Please log into the site first', 'wiloke-google-authenticator')
                ]);
            }
            
            $oUser = new \WP_User($userId);
            if (empty($oUser) || is_wp_error($oUser)) {
                wp_send_json_error([
                  'msg' => esc_html__('Please log into the site first', 'wiloke-google-authenticator')
                ]);
            }
            
            if (!isset($_POST['otp_code']) || empty($_POST['otp_code'])) {
                wp_send_json_error([
                  'msg' => esc_html__('Verification failed. Please try it again.', 'wiloke-google-authenticator')
                ]);
            }
            
            try {
                if (GoogleAuthenticator::verifyTwoFactorCode($_POST['otp_code'], $userId)) {
                    Session::isNeedToCheckOTP(true);
                    wp_set_auth_cookie($oUser->ID, true);
                    wp_send_json_success([
                      'redirectTo' => Cookie::getCookieRedirectTo(true)
                    ]);
                } else {
                    wp_send_json_error([
                      'msg' => esc_html__('Verification failed. Please try it again.', 'wiloke-google-authenticator')
                    ]);
                }
            } catch (\Exception $e) {
                wp_send_json_error([
                  'msg' => $e->getMessage()
                ]);
            }
        }
        
        wp_send_json_error([
          'msg' => esc_html__('This feature is turning off', 'wiloke-google-authenticator')
        ]);
    }
    
    public function modifyLoginRedirection($redirectTo)
    {
        global $pagenow;
        if (Session::isNeedToCheckOTP(true) && $pagenow === 'wp-login.php') {
            wp_logout();
            
            return get_permalink(GetOption::getOptionDetail('wga_verify_page'));
        }
        
        return $redirectTo;
    }
    
    public function checkOtp($oUser)
    {
        if (empty($oUser) || is_wp_error($oUser)) {
            return $oUser;
        }
        
        try {
            if (User::isEnableGoogleAuth($oUser->ID) && User::isLockedQrCode($oUser->ID)) {
                Session::setCurrentUserId($oUser->ID);
                Session::setNeedToCheckOTP();
            }
        } catch (\Exception $e) {
            return $oUser;
        }
        
        return $oUser;
    }
}
