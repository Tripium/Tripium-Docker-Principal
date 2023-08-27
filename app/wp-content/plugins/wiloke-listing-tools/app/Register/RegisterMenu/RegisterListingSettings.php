<?php

namespace WilokeListingTools\Register\RegisterMenu;

use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\Inc;
use WilokeListingTools\Register\ListingToolsGeneralConfig;

class RegisterListingSettings implements InterfaceRegisterMenu
{
    use ListingToolsGeneralConfig;
    private $aListingsGroup;

    public function registerMenu()
    {
        if (!empty($aListingTypes = General::getPostTypesGroup('listing'))) {
            foreach ($aListingTypes as $menuSlug => $aListingType) {
                add_submenu_page(
                    $this->parentSlug,
                    $aListingType['menu_name'],
                    $aListingType['menu_name'],
                    'manage_options',
                    $aListingType['menu_slug'],
                    [$this, 'registerSettingArea']
                );
            }
        }
    }

    public function registerSettingArea()
    {
        Inc::file('listing-settings:index');
    }
}
