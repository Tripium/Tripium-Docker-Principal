<?php
/*
 * Plugin Name: Wilcity App Deep Link
 * Plugin URI: https://wilcity.com
 * Author: Wilcity
 * Author URI: https://wiloke.com
 * Version: 1.0.2
 * Description: Displaying Instagram on the Listing area
 * Domain Path: /languages/
 */

use WilcityAppDeepLink\Controllers\ThemeOptionsController;
use \WilcityAppDeepLink\Controllers\SingleController;

define('WILCITY_APP_DEE_LINK_VERSION', '1.0.2');
define('WILCITY_APP_DEE_LINK_PATH', plugin_dir_path(__FILE__));
define('WILCITY_APP_DEE_LINK_URL', plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

new ThemeOptionsController();
new SingleController();
