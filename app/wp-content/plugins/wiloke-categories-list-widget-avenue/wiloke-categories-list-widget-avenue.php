<?php

/**
* Tested up to:        5.6.2
* Domain Path:         /languages
* Text Domain:         wiloke-post-categories-avenue
* License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
* License:             GPL-2.0+
* Author URI:          https://wiloke.com
* Author:              wiloke
* Version:             1.0.19
* Description:         Wiloke Categories List Widget Avenue for Elementor
* Plugin URI:          https://wiloke.com
* Plugin Name:         Wiloke Categories List Widget Avenue
*/

define("WILOKE_WILOKEPOSTCATEGORIESAVENUE_VERSION",defined('WP_DEBUG') && WP_DEBUG ? uniqid() : '1.0.19');
define("WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE", "wiloke-post-categories-avenue");
define("WILOKE_WILOKEPOSTCATEGORIESAVENUE_PREFIX", "wiloke-post-categories-avenue_");
define("WILOKE_WILOKEPOSTCATEGORIESAVENUE_VIEWS_PATH", plugin_dir_path(__FILE__));
define("WILOKE_WILOKEPOSTCATEGORIESAVENUE_VIEWS_URL", plugin_dir_url(__FILE__));


add_action("plugins_loaded", "WilokePostCategoriesAvenueLoadPluginDomain");
if (!function_exists("WilokePostCategoriesAvenueLoadPluginDomain")) {
	function WilokePostCategoriesAvenueLoadPluginDomain()
	{
		load_plugin_textdomain("wiloke-post-categories-avenue", false, plugin_dir_path(__FILE__) . "languages");
	}
}

require_once plugin_dir_path(__FILE__) . "vendor/autoload.php";

new \WilokePostCategoriesAvenue\Controllers\RegistrationController();
new \WilokePostCategoriesAvenue\Controllers\HandleAjaxController();