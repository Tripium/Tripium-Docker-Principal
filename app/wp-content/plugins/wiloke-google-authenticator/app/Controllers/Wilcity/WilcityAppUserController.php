<?php

namespace WilokeGoogleAuthenticator\Controllers\Wilcity;

use WilokeGoogleAuthenticator\Helpers\GoogleAuthenticator;
use WilokeGoogleAuthenticator\Helpers\User;

class WilcityAppUserController
{
    public function __construct()
    {
        add_filter('wilcity/filter/wilcity-mobile-app/app/Controllers/UserController/getProfileFields/oBasicInfo',
          [$this, 'addToggleGoogleAuthToSettings'], 10, 1);
        add_filter('wilcity/filter/wilcity-mobile-app/app/Controllers/JsonSkeleton/getUserProfile/oBasicInfo',
          [$this, 'getProfileInfo'], 10, 2);
    }
    
    public function getProfileInfo($aValues, $userId)
    {
        if (!\WilokeThemeOptions::isEnable('wga_toggle')) {
            return $aValues;
        }
        
        try {
            $aValues['toggle_two_factor_auth'] = User::isEnableGoogleAuth($userId);
        } catch (\Exception $exception) {
        }
        
        return $aValues;
    }
    
    public function addToggleGoogleAuthToSettings($aFields)
    {
        if (!\WilokeThemeOptions::isEnable('wga_toggle')) {
            return $aFields;
        }
        try {
            $aFields[] = [
              'label' => 'twofactorauth',
              'key'   => 'toggle_two_factor_auth',
              'type'  => 'switch'
            ];
        } catch (\Exception $e) {
        }
        
        return $aFields;
    }
}
