<?php
/*
 * Plugin Name: Wilcity Notification Bar
 * Plugin URI: https://wiloke.com
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Domain Path: /languages
 * Text Domain: wiloke-notification-bar-wp
 * Version: 1.0
 */


use WilokeNotificationBar\Controllers\AdminHandlingInSettings;
use WilokeNotificationBar\Helpers\App;

define('WILOKE_NB_VERSION', '1.0');

define('WILOKE_NB_DIR_PATH', plugin_dir_path(__FILE__));
define('WILOKE_NB_DIR_URL', plugin_dir_url(__FILE__));

require_once WILOKE_NB_DIR_PATH . 'vendor/autoload.php';
require_once WILOKE_NB_DIR_PATH . 'functions/function.php';

WilokeNotificationBar\Helpers\App::bind('config/Settings', require_once WILOKE_NB_DIR_PATH . 'configs/settings.php');
App::bind('adminSetting', new AdminHandlingInSettings());
new \WilokeNotificationBar\Controllers\AdminSettingNBController();
new AdminHandlingInSettings();
new \WilokeNotificationBar\Controllers\DisplayBannerToFrontEndController();

