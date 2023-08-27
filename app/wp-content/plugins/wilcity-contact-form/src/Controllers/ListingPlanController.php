<?php

namespace WilcityContactForm\Controllers;

class ListingPlanController
{
    public function __construct()
    {
        add_filter(
          'wilcity/filter/wiloke-listing-tools/config/listing-plan/listing_plan_settings',
          [$this, 'addToggleContactForm7']
        );
    }
    
    public function addToggleContactForm7($aFields)
    {
        $aFields[] = [
          'type'      => 'wiloke_field',
          'fieldType' => 'select',
          'id'        => 'add_listing_plan:toggle_contact_form',
          'name'      => 'Toggle Contact Form',
          'options'   => [
            'enable'  => 'Enable',
            'disable' => 'Disable'
          ]
        ];
        
        return $aFields;
    }
}
