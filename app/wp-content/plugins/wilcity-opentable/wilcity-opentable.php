<?php
/**
 * Plugin Name: Wilcity OpenTable
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Version: 1.0.3
 * Description: Allow customer to embed Open Table onto their listing
 * Plugin URI:https://wilcityservice.com/wilcity-opentable
 * Text Domain: wilcity-opentable
 * Domain Path: /languages
 */

use WilcityOpenTable\Helpers\App;
use WilcityOpenTable\Controllers\RegisterOpenTableController;
use WilcityOpenTable\Controllers\FetchRestaurantController;
use WilcityOpenTable\Controllers\SaveOpenTableController;
use WilcityOpenTable\Controllers\AddListingController;
use WilcityOpenTable\Controllers\SingleNavigationController;
use WilcityOpenTable\Controllers\SingleSidebarController;
use WilcityOpenTable\Controllers\ShortcodeController;
use WilcityOpenTable\Controllers\OpentablePlanSetting;
use WilcityOpenTable\Controllers\EnqueueScripts;
use WilcityOpenTable\Controllers\AdminNotification;

add_action('wiloke-listing-tools/run-extension', function () {
	if (class_exists('WilokeListingTools\Framework\Helpers\App')) {
		define('WILCITY_OPENTABLE_URL', plugin_dir_url(__FILE__));
		define('WILCITY_OPENTABLE_VERSION', '1.0.2');

		require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
		App::bind('app', include plugin_dir_path(__FILE__) . 'config/app.php');

		if (!App::isRequiredSetup()) {
			if (is_admin()) {
				new RegisterOpenTableController;
				new OpentablePlanSetting;
			} else {
				new AddListingController;
				new ShortcodeController;
				new EnqueueScripts;
			}

			new FetchRestaurantController;
			new SaveOpenTableController;
			new SingleNavigationController;
			new SingleSidebarController;
		}

		new AdminNotification;
	}
});
