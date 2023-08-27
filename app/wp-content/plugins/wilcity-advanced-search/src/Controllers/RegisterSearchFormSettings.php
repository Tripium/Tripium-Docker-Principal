<?php

namespace WilcityAdvancedSearch\Controllers;

use WilcityAdvancedSearch\Helpers\App;

class RegisterSearchFormSettings
{
    public function __construct()
    {
        add_filter('wilcity/theme-options/configurations', [$this, 'registerSettings'], 10, 2);
    }
    
    /**
     * @param $aOptions
     *
     * @return array
     */
    public function registerSettings($aOptions, $aArgs)
    {
        App::bind('app', include WILCITY_ADVANCED_SEARCHFORM_DIR.'config/app.php');
        
        return array_reduce($aOptions, function ($aCarry, $aItem) {
            if ($aItem['id'] === 'search_settings') {
                array_push($aCarry, $aItem);
                return array_merge($aCarry, App::get('app')['themeoptions']);
            }
            
            array_push($aCarry, $aItem);
            
            return $aCarry;
        }, []);
    }
}
