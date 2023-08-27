<?php
/**
 * Plugin Name: Wilcity HsBlog Plugin
 * Author: Wiloke
 * Author URI: https://wilcity.com
 * Plugin URI: https://wilcity.com
 * Version: 1.0
 * Description: The plugin helps to integrate HsBlog Shortcode to Wilcity
 */

use WilcityHsBlog\Controllers\ThemeOptionsController;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

define('WILCITY_HSBLOG_DIR', plugin_dir_path(__FILE__));
define('WILCITY_HSBLOG_URL', plugin_dir_url(__FILE__));

global $aWilcityHsBlogObjects;
$aWilcityHsBlogObjects['ShortcodeController'] = new \WilcityHsBlog\Controllers\ShortcodeController();
new ThemeOptionsController;
