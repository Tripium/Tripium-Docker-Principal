<?php

namespace WilcityAdvancedProducts\Controllers;

class SingleNavigationController
{
    public function __construct()
    {
        add_filter(
          'wilcity/filter/single-listing/home/navigation-dir', [$this, 'replaceNewMyProduct'],
          10,
          2
        );
        
        add_filter(
          'wilcity/wiloke-listing-tools/filter/configs/single-nav/fields/status/conditional/excludes',
          [$this, 'addExcludeSingleNaviationSetting']
        );
    }
    
    public function addExcludeSingleNaviationSetting($aExcludes)
    {
        $aExcludes[] = 'my_advanced_products';
        
        return $aExcludes;
    }
    
    protected function getFile($file)
    {
        return WILCITY_ADVANCED_WOOCOMMERCE.'template/'.$file.'.php';
    }
    
    public function replaceNewMyProduct($fileDir, $aContentSettings)
    {
        if ($aContentSettings['baseKey'] !== 'my_advanced_products' ||
            !isset($aContentSettings['variant']) ||
            !is_file($this->getFile($aContentSettings['variant']))
        ) {
            return $fileDir;
        }
        
        return $this->getFile($aContentSettings['variant']);
    }
}
