<?php
/*
 * Plugin Name: Wilcity WPML
 * Plugin URI: https://wilcity.com
 * Author: Wilcity
 * Author URI: https://wilcity.com
 * Version: 1.0.0
 * Description: Enabling WPML on the mobile app
 */

use WilcityWPMLAPP\Helpers\App;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('wiloke-listing-tools/run-extension', function () {
	if (!App::isRequiredSetup()) {
		new \WilcityWPMLAPP\Controllers\LanguageController();
	}
});
