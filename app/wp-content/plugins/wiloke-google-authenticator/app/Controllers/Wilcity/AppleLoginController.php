<?php

namespace WilokeGoogleAuthenticator\Controllers\Wilcity;

use WilokeGoogleAuthenticator\Helpers\Cookie;
use WilokeGoogleAuthenticator\Helpers\GetOption;
use WilokeGoogleAuthenticator\Helpers\Session;

class AppleLoginController
{
    public function __construct()
    {
        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Controllers/AppleLoginController/listenToAppleLoginRedirection/response',
          [$this, 'addNeedVerifyOTPToLoginResponse']
        );
    }
    
    public function addNeedVerifyOTPToLoginResponse($aResponse)
    {
        if (!Session::isNeedToCheckOTP()) {
            return $aResponse;
        }
        
        wp_logout();
        Cookie::setCookieRedirectTo($aResponse['redirectTo']);
    
        $aResponse['redirectTo'] = add_query_arg(
          [
            'action' => 'wilcity_verify_otp'
          ],
          get_permalink(GetOption::getOptionDetail('custom_login_page'))
        );
        
        return $aResponse;
    }
}
