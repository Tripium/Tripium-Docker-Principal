<?php
/**
 * Plugin Name: Wilcity App AddListing
 * Plugin URI:  https://wilcityservice.com
 * Description: Integrating Wilcity AddListing to Wilcity App
 * Author: Wiloke
 * Author URI: https://wilcityservice.com
 * Version: 1.0.2
 * Text Domain: wiloke-otp-login
 * Domain Path: /languages
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

define('WILCITY_ADDLISTING_ON_APP_PATH', plugin_dir_path(__FILE__));
define('WILCITY_ADDLISTING_ON_APP_URL', plugin_dir_url(__FILE__));
define('WILCITY_ADDLISTING_ON_APP_VERSION', 'v1');

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

new \WilcityAddListingOnApp\Controllers\ThemeOptionsController();
new \WilcityAddListingOnApp\Controllers\DashboardController();
