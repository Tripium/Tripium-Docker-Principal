<?php
/*
 * Plugin Name: Wilcity Instafeed Hub
 * Plugin URI: https://wilcity.com
 * Author: Wilcity
 * Author URI: https://wiloke.com
 * Version: 1.1.5
 * Description: Displaying Instagram on the Listing area
 * Domain Path: /languages/
 */

use WilokeInstagramFeedhub\Controllers\AddListingController;
use WilokeInstagramFeedhub\Controllers\ListingMetaBoxes;
use WilokeInstagramFeedhub\Helpers\App;
if (!defined('WILOKE_IFH_NAMESPACE')) {
	define('WILOKE_IFH_NAMESPACE', 'wiloke/v1/instafeedhub');
}

define('WILOKE_INSTAFEEDHUB_VERSION', '1.1.5');
define('WILOKE_INSTAFEEDHUB_NAMESPACE', 'wilcity-instafeed-hub/v1');
define('WILOKE_INSTAFEEDHUB_REST', 'https://instafeedhub.com/wp-json/wiloke/v1/instagram/');
define('WILOKE_INSTAFEEDHUB_LOGIN_REST', 'https://instafeedhub.com/wp-json/instafeedhub/v1/');
define('WILOKE_INSTAFEEDHUB_URL', 'https://instafeedhub.com/');
define('WILOKE_INSTAFEEDHUB_DASHBOARD_URL', 'https://instafeedhub.com/insta-dashboard/');
define('WILCITY_INSTAFEED_HUB_URL', plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('wiloke-listing-tools/run-extension', function () {
	App::bind('configs/addlisting', include plugin_dir_path(__FILE__) . 'configs/addlisting.php');
	App::bind('configs/metaboxes', include plugin_dir_path(__FILE__) . 'configs/metaboxes.php');
	App::bind('configs/navigation', include plugin_dir_path(__FILE__) . 'configs/navigation.php');
	App::bind('configs/sidebar', include plugin_dir_path(__FILE__) . 'configs/sidebar.php');

	App::bind('WilcityInstafeedHubAddListingController', new AddListingController);
	new \WilokeInstagramFeedhub\Controllers\ListenToTokenController();
	new \WilokeInstagramFeedhub\Controllers\RemoteDataController();
	new \WilokeInstagramFeedhub\Controllers\SingleNavigationController();
	new \WilokeInstagramFeedhub\Controllers\SingleSidebarController();
	new \WilokeInstagramFeedhub\Controllers\ShortcodeController();
	new \WilokeInstagramFeedhub\Controllers\AdminController();

	if (is_admin()) {
		new ListingMetaBoxes;
	}
});
