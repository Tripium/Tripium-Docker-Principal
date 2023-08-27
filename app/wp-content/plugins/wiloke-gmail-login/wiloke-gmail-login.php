<?php
/**
 * Plugin Name: Wiloke Gmail Login
 * Author: Wiloke
 * Version: 1.1.6
 * Language: wiloke-gmail-login
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

define('WILOKE_GMAIL_VER', '1.1.6');
define('WILOKE_GMAIL_VERSION', 'v3');
define('WILOKE_API_GMAIL', 'wiloke');

define('WILOKE_GMAIL_VIEWS', plugin_dir_path(__FILE__) . 'app/Views/');

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use WilokeGmailLogin\Controllers\AdminMenuController;
use WilokeGmailLogin\Controllers\GmailLoginController;
use WilokeGmailLogin\Controllers\Wilcity\CustomLoginController;
use WilokeGmailLogin\Controllers\Wilcity\GmailVerificationController;
use WilokeGmailLogin\Controllers\Wilcity\PopupSigninController;

new AdminMenuController();
new GmailLoginController();
new PopupSigninController();
new GmailVerificationController();
new CustomLoginController();
