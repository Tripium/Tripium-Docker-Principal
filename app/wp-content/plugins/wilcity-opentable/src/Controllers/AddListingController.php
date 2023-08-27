<?php

namespace WilcityOpenTable\Controllers;

use WilokeListingTools\Framework\Helpers\GetSettings;

class AddListingController
{
    public function __construct()
    {
        add_filter('wilcity/filter/wiloke-listing-tools/add-listing-settings/results', [$this, 'printResults'], 10, 4);
    }
    
    /**
     * @param $aValues
     * @param $listingID
     * @param $planID
     * @param $aSections
     *
     * @return mixed
     */
    public function printResults($aValues, $listingID, $planID, $aSections)
    {
        $aOpenTable = array_filter($aSections, function ($aItem) {
            return $aItem['type'] === 'opentable';
        });
        
        if (!empty($aOpenTable)) {
            $aOpenTable = [];
            
            $aOpenTable['id'] = GetSettings::getPostMeta($listingID, 'opentable_id');
            if (!empty($aOpenTable['id'])) {
                $aOpenTable['label'] = GetSettings::getPostMeta($listingID, 'opentable_name');
                $aValues['opentable'] = [
                  'my_opentable' => $aOpenTable
                ];
            }
        }
        
        return $aValues;
    }
}
