<?php

namespace WilcityOpenTable\Helpers;

use WilokeListingTools\Framework\Helpers\GetSettings;

class Opentable
{
    private static $endpoint = 'https://www.opentable.com/widget/reservation/restaurant-search';
    
    public static function getListingOpenTable($postID)
    {
        $id = GetSettings::getPostMeta($postID, 'opentable_id');
        if (empty($id)) {
            return false;
        }
        
        $name = GetSettings::getPostMeta($postID, 'opentable_name');
        
        return [
          'id'    => $id,
          'label' => $name
        ];
    }
    
    /**
     * @param $search
     *
     * @return bool|array
     */
    public static function searchByName($search)
    {
        $request = wp_remote_get(
          add_query_arg(
            [
              'query' => $search
            ],
            self::$endpoint
          )
        );
        
        if (is_wp_error($request)) {
            return false; // Bail early
        }
        
        $body      = wp_remote_retrieve_body($request);
        $aRawItems = json_decode($body, true);
        if (empty($aRawItems) || empty($aRawItems['items'])) {
            return false;
        }
        
        return $aRawItems['items'];
    }
    
    /**
     * @param $id
     *
     * @return bool|mixed|null
     */
    public static function searchByRestaurantID($id)
    {
        $aItems = self::searchByName($id);
        if (!$aItems) {
            return false;
        }
    
        return $aItems[0];
    }
    
    public static function buildRestaurantName($aItem)
    {
        return $aItem['name'].' ('.$aItem['addressResponse']['address1'].' '.$aItem['addressResponse']['city'].' '
               .$aItem['addressResponse']['country'].')';
    }
}
