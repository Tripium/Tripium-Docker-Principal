<?php

namespace WilcityContactForm\Controllers;

class ThemeOptionsController
{
    public function __construct()
    {
        add_filter('wilcity/theme-options/configurations', [$this, 'addFields']);
    }
    
    public function addFields($aOptions)
    {
        return array_reduce($aOptions, function ($aCarry, $aItem) {
            if ($aItem['id'] === 'listing_settings') {
                $aItem['fields'] = array_merge($aItem['fields'], wilcityContactFormRepository()->setFile('themeoptions')->getAll());
            }
        
            array_push($aCarry, $aItem);
        
            return $aCarry;
        }, []);
    }
    
}
