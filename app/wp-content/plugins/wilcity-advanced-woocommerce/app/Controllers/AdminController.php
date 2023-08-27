<?php

namespace WilcityAdvancedProducts\Controllers;

class AdminController
{
    public function __construct()
    {
        add_filter('wilcity/wiloke-listing-tools/configs/filter/listing-settings', [$this, 'switchMyRoomToMultiple']);
        add_filter('wilcity/wiloke-listing-tools/metaboxes/event/filter/save-my-room', [$this, 'saveMultipleRooms'],
            10, 2);
        add_filter('wilcity/wiloke-listing-tools/metaboxes/listing/filter/get-my-room', [$this, 'getMyRoom']);
    }
    
    public function saveMultipleRooms($ignoreIt, $aProductIDs)
    {
        $aCleanProductIDs = [];
        foreach ($aProductIDs as $productID) {
            $productID = abs(trim($productID));
            if (get_post_type($productID) != 'product') {
                continue;
            }
            
            $aCleanProductIDs[] = $productID;
        }
        
        return implode(',', $aCleanProductIDs);
    }
    
    public function switchMyRoomToMultiple($aFields)
    {
        $aFields['myRoom']['fields'][0]['multiple'] = true;
        
        return $aFields;
    }
    
    public function getMyRoom($productIDs)
    {
        return implode(',', $productIDs);
    }
}
