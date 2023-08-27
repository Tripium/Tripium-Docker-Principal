<?php

namespace WilcityOpenTable\Controllers;

use WilcityOpenTable\Helpers\App;
use WilokeListingTools\Framework\Routing\Controller;

class RegisterOpenTableController extends Controller
{
    public function __construct()
    {
        add_filter('wilcity/filter/wiloke-listing-tools/configs/settings', [$this, 'registerOpenTableToFrontend']);
        add_action('cmb2_admin_init', [$this, 'registerOpenTableToBackend'], 10);
    }
    
    public function registerOpenTableToFrontend($aSections)
    {
        $aSections = array_merge($aSections, App::get('app')['fields']);
        
        return $aSections;
    }
    
    public function registerOpenTableToBackend()
    {
        if (!$this->isCurrentAdminListingType() || $this->isDisableMetaBlock(['fieldKey' => 'opentable'])) {
            return false;
        }
        
        new_cmb2_box(App::get('app')['metabox']['opentable']);
    }
}
