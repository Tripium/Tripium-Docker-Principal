<?php

namespace WilcityOpenTable\Controllers;

use WilcityOpenTable\Helpers\App;

class SingleSidebarController
{
    public function __construct()
    {
        add_filter('wilcity/wiloke-listing-tools/filter/sidebar-items', [$this, 'addOpentableToSingleSidebar']);
        add_filter('wilcity/wiloke-listing-tools/filter/sidebar-machine', [$this, 'addOpentableShortcodeToSidebar']);
        add_filter('wilcity/wiloke-listing-tools/filter/sidebar-fields', [$this, 'addOpenTableSingleSidebarSettings']);
    }
    
    public function addOpenTableSingleSidebarSettings($aItems)
    {
        $aItems['sections'] = array_merge($aItems['sections'], App::get('app')['sidebar']['settings']);
        return $aItems;
    }
    
    public function addOpentableToSingleSidebar($aItems)
    {
        $aItems['opentable'] = App::get('app')['sidebar']['default'];
        
        return $aItems;
    }
    
    public function addOpentableShortcodeToSidebar($aShortcodes)
    {
        $aShortcodes['opentable'] = 'wilcity_sidebar_opentable';
        
        return $aShortcodes;
    }
}
