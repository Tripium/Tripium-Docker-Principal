<?php

namespace WilokeGoogleAuthenticator\Controllers;

class ThemeOptionController
{
    public function __construct()
    {
        add_filter('wilcity/theme-options/configurations', [$this, 'addWilcityThemeOptions']);
    }
    
    public function addWilcityThemeOptions($aThemeOptions)
    {
        $aFields                                   = $aThemeOptions['register_login']['fields'];
        $aFields                                   = array_merge(
          $aFields,
          include WILOKE_GOOGLE_AUTHENTICATOR_PATH.'config/wilcity-themeoptions.php'
        );
        $aThemeOptions['register_login']['fields'] = $aFields;
        
        return $aThemeOptions;
    }
}
