<?php

namespace WilcityAdvancedProducts\Controllers;

use WilokeListingTools\Register\ListingToolsGeneralConfig;

class AdminSettingController
{
    use ListingToolsGeneralConfig;
    private $slug = 'wilcity-advanced-woocomerce';
    
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenus']);
    }
    
    public function registerMenus()
    {
        add_submenu_page(
          $this->parentSlug,
          'Advanced WooCommerce Settings',
          'Advanced WooCommerce Settings',
          'administrator', $this->slug,
          [$this, 'advancedWooCommerceSettings']
        );
    }
}
