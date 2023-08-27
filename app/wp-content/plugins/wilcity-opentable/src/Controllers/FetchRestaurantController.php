<?php

namespace WilcityOpenTable\Controllers;

use WilcityOpenTable\Helpers\Opentable;
use WilokeListingTools\Controllers\Retrieve\AjaxRetrieve;
use WilokeListingTools\Controllers\Retrieve\RestRetrieve;
use WilokeListingTools\Controllers\RetrieveController;

class FetchRestaurantController
{
    public function __construct()
    {
        add_action('wp_ajax_wilcity_fetch_my_opentable', [$this, 'fetchRestaurant']);
    }
    
    public function fetchRestaurant()
    {
        $isBackend = false;
        if (isset($_GET['search'])) {
            $search = trim($_GET['search']);
        } else if (isset($_GET['q'])) {
            $isBackend = true;
            $search    = trim($_GET['q']);
        }
        
        $aOpenTableItems = Opentable::searchByName($search);
        
        $oRetrieve = new RetrieveController(new AjaxRetrieve());
        
        if (!$aOpenTableItems) {
            return $oRetrieve->error([
              'msg' => esc_html__('We found no restaurant yet, please try with another keyword', 'wilcity-opentable')
            ]);
        }
        $aItems = [];
        foreach ($aOpenTableItems as $aItem) {
            $name = Opentable::buildRestaurantName($aItem);
            
            $aItems[] = [
              'id'    => $aItem['rid'],
              'label' => $name,
              'text'  => $name
            ];
            
        }
        
        if ($isBackend) {
            return $oRetrieve->success([
              'msg' => [
                'results' => $aItems
              ]
            ]);
        }
        
        return $oRetrieve->success(['results' => $aItems]);
    }
}
