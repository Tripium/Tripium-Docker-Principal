<?php
/**
 * Plugin Name: Wilcity Advanced Search
 * Plugin URI: https://wilcityservice.com/wiloke-plugins/wilcity-advanced-search/
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Version: 1.3.1
 * Description: This plugin is an extension of Wilcity Theme
 * Domain Path: /languages/
 */

define('WILCITY_ADVANCED_CONFIG_DIR', plugin_dir_path(__FILE__) . 'config/');

use WilcityAdvancedSearch\Controllers\ProductSearchController;
use WilcityAdvancedSearch\Controllers\RegisterProductCard;
use WilcityAdvancedSearch\Controllers\RegisterShopSearchMenu;
use WilcityAdvancedSearch\Controllers\RegisterSearchFormSettings;
use WilcityAdvancedSearch\Controllers\ModifySearchQueryController;
use WilcityAdvancedSearch\Controllers\ModifySearchFieldsController;
use WilcityAdvancedSearch\Controllers\AdminNotification;

add_action('wiloke-listing-tools/run-extension', 'loadWilcityAdvancedSearch');

function loadWilcityAdvancedSearch()
{
	define('WILCITY_ADVANCED_SEARCHFORM_DIR', plugin_dir_path(__FILE__));
	define('WILCITY_ADVANCED_SEARCHFORM_URI', plugin_dir_url(__FILE__));

	require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

	if (!\WilcityAdvancedSearch\Helpers\App::isRequiredSetup()) {
		if (is_admin()) {
			new RegisterSearchFormSettings;
			new RegisterProductCard;
		}

		new ModifySearchQueryController;
		new ModifySearchFieldsController;
	}

	new AdminNotification;
	new RegisterShopSearchMenu;

	if (class_exists('WilokeListingTools\Register\RegisterMenu\RegisterListingScripts')) {
		new ProductSearchController();
	}
}
