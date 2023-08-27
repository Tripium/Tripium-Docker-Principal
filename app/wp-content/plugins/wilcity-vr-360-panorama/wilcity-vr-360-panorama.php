<?php
/*
 * Plugin Name: Wilcity VR 360 Panorama
 * Plugin URI: https://wilcityservice.com
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Version: 1.0
 * Description: Adding 360 Panorama to Top of Single Listing page
 * Text Domain: wilcity-vr-360-panorama
 * Domain Path: /languages/
 */

use WilcityVR\Controllers\AddListingController;
use WilcityVR\Controllers\AdminWarningController;
use WilcityVR\Controllers\HeaderController;
use WilcityVR\Controllers\ListingPlanController;
use WilcityVR\Helpers\App;
use WilcityVR\MetaBoxes\AddMetaBox;
use WilcityVR\Shortcodes\RegisterIFrameShortcode;

define('WILCITY_VR_PATH', plugin_dir_path(__FILE__));
define('WILCITY_VR_CONFIG_PATH', WILCITY_VR_PATH . 'configs/');
define('WILCITY_VR_VIEWS_PATH', WILCITY_VR_PATH . 'app/Views/');


require_once WILCITY_VR_PATH . 'vendor/autoload.php';
App::bind('addlisting', include WILCITY_VR_CONFIG_PATH . 'addlisting.php');
App::bind('metabox', include WILCITY_VR_CONFIG_PATH . 'metabox.php');
App::bind('listingplan', include WILCITY_VR_CONFIG_PATH . 'listingplan.php');

add_action('wiloke-listing-tools/run-extension', function () {
	if (defined('WILCITYSERIVCE_CLIENT_DIR')) {
		new AddListingController;
		new RegisterIFrameShortcode;

		if (is_admin()) {
			new AddMetaBox;
			new ListingPlanController;
		} else {
			new HeaderController;
		}
	}

	new AdminWarningController();
});
