<?php

namespace WilcityAdvancedProducts\Controllers;

class ThemeOptionsController extends Controller
{
    public function __construct()
    {
        add_filter('wilcity/theme-options/configurations', [$this, 'addThemeOptionSettings']);
    }
    
    public function addThemeOptionSettings($aOptions)
    {
        $aOptions['woocommerce_advanced_settings'] = wilcityAdvancedWooCommerceGetFile()
          ->setFile('themeoptions')
          ->getAll()
        ;
        
        return $aOptions;
    }
}
