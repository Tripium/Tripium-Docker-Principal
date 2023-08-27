<?php

namespace WilokeGoogleAuthenticator\Controllers\Wilcity;

use WilokeGoogleAuthenticator\Helpers\Cookie;
use WilokeGoogleAuthenticator\Helpers\GetOption;
use WilokeGoogleAuthenticator\Helpers\Session;

class FacebookLoginController
{
    public function __construct()
    {
        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Controllers/RegisterLoginController/handleLogin/response',
          [$this, 'addNeedVerifyOTPToLoginResponse'],
          10,
          2
        );
    }
    
    public function addNeedVerifyOTPToLoginResponse($aResponse)
    {
        if (!Session::isNeedToCheckOTP()) {
            return $aResponse;
        }
        
        wp_logout();
        if (GetOption::isEnable('toggle_custom_login_page')) {
            Cookie::setCookieRedirectTo($aResponse['redirectTo']);
            
            $aResponse['redirectTo'] = add_query_arg(
              [
                'action' => 'wilcity_verify_otp'
              ],
              get_permalink(GetOption::getOptionDetail('custom_login_page'))
            );
        } else {
            $aResponse['next'] = 'verifyOTP';
        }
        
        return $aResponse;
    }
}
