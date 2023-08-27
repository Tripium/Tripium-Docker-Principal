<?php

namespace WilcityOpenTable\Controllers;

class OpentablePlanSetting
{
    public function __construct()
    {
        add_filter(
          'wilcity/filter/wiloke-listing-tools/config/listing-plan/listing_plan_settings',
          [$this, 'addToggleOpentableToListingPlanSettings']
        );
    }
    
    public function addToggleOpentableToListingPlanSettings($aPlanSettings)
    {
        array_push($aPlanSettings, [
          'type'      => 'wiloke_field',
          'fieldType' => 'select',
          'id'        => 'add_listing_plan:toggle_opentable',
          'name'      => 'Toggle Opentable',
          'options'   => [
            'enable'  => 'Enable',
            'disable' => 'Disable'
          ]
        ]);
        
        return $aPlanSettings;
    }
}
