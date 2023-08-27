<?php

namespace WilcityAdvancedProducts\Controllers;

class RegisterProductsNavigationController
{
    public function __construct()
    {
        add_filter(
          'wilcity/wiloke-listing-tools/filter/configs/listing-settings/navigation/draggable',
          [$this, 'addNewProductStyleToNavigation']
        );
        
        add_filter(
          'wilcity/wiloke-listing-tools/filter/configs/single-nav/fields/sections',
          [$this, 'addSectionFields'],
          10
        );
    }
    
    public function addSectionFields($aFields)
    {
        $aFields =
          array_merge($aFields, wilcityAdvancedWooCommerceGetFile()->setFile('single-navigation')->get('settings'));
        
        return $aFields;
    }
    
    public function addNewProductStyleToNavigation($aItems)
    {
        $aItems = array_merge(
          $aItems,
          wilcityAdvancedWooCommerceGetFile()
            ->setFile('single-navigation')
            ->get('sections')
        );
        
        return $aItems;
    }
}
