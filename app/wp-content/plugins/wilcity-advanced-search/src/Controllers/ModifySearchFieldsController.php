<?php

namespace WilcityAdvancedSearch\Controllers;

class ModifySearchFieldsController
{
    private $aSearchTarget;
    
    public function __construct()
    {
        add_filter('wilcity/filter/wiloke-listing-tools/hero-search-form/fields', [$this, 'addSearchTypeToHeroFields']);
    }
    
    public function addSearchTypeToHeroFields($aFields)
    {
        $this->aSearchTarget = \WilokeThemeOptions::getOptionDetail('complex_search_target');
        if (empty($this->aSearchTarget) || !is_array($this->aSearchTarget) || empty($this->aSearchTarget['enabled'])) {
            return $aFields;
        }
        
        unset($this->aSearchTarget['enabled']['placebo']);
        
        return array_map(function ($aField) {
            if ($aField['key'] === 'complex') {
                $aField['searchTarget'] = array_keys($this->aSearchTarget['enabled']);
                $aField['module']       = 'complex';
            }
            
            return $aField;
        }, $aFields);
    }
}
