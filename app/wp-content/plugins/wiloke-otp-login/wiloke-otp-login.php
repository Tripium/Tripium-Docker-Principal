<?php
/**
 * Plugin Name: Wiloke OTP Login
 * Plugin URI: https://wiloke.com/
 * Description: One Time Login Plugin
 * Author: Wiloke
 * Author URI: https://wiloke.com/
 * Version: 1.1.1
 * Text Domain: wiloke-otp-login
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}
define('WILOKE_API_OTP','wiloke');
define('WILOKE_API_OTP_VERSION','v3');
define('WILOKE_OTP_VIEWS', plugin_dir_path(__FILE__).'app/Views/');
define('WILOKE_OTP_CONFIGS', plugin_dir_path(__FILE__).'configs/');

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('plugins_loaded', function(){
	load_plugin_textdomain('wiloke-otp-login', false, basename(dirname(__FILE__)) . '/languages');
});

use WilokeOTPLogin\Controllers\AdminMenuController;
use WilokeOTPLogin\Controllers\Wilcity\CustomLoginPageController;
use WilokeOTPLogin\Controllers\Wilcity\PopupLoginController;
use WilokeOTPLogin\Controllers\Wilcity\WilcityHandleOTPCode;

new AdminMenuController();
new PopupLoginController();
new \WilokeOTPLogin\Controllers\HandleController();
new WilcityHandleOTPCode();
new CustomLoginPageController();
