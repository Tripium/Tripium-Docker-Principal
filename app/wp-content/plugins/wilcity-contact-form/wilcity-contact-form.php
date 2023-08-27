<?php
/*
 * Plugin Name: Wilcity Contact Form
 * Plugin URI: https://wilcity.com
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Description: Allow adding Contact Form 7 to Listing Sidebar
 * Version: 1.1.3
 */

use WilcityContactForm\Controllers\ListingPlanController;
use WilcityContactForm\Controllers\RegisterSidebarController;
use WilcityContactForm\Controllers\ContactFormSevenController;
use WilcityContactForm\Controllers\ThemeOptionsController;
use WilcityContactForm\Controllers\SidebarOrderController;
use WilcityContactForm\Helpers\App;
use WilokeRepository\Helpers\WilokeRepository;

add_action('wiloke-listing-tools/run-extension', function () {
	require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

	if (!App::isRequiredSetup()) {
		function wilcityContactFormRepository()
		{
			return WilokeRepository::init(plugin_dir_path(__FILE__) . 'configs/');
		}

		if (is_admin()) {
			new RegisterSidebarController;
			new ThemeOptionsController;
			new ListingPlanController;
		} else {
			new SidebarOrderController;
		}

		new ContactFormSevenController;
	}
});

