<?php
/**
 * Plugin name: Wilcity Translation Helper
 * Author: Wilcity
 * Author URI: https://wilcity.com
 * Plugin URI: https://wilcity.com
 * Version: 1.0
 * Description: Translating Wilcity Easier
 */

require_once plugin_dir_path(__FILE__).'vendor/autoload.php';

define('WILCITY_TRANSLATION_VERSION', 1.0);
define('WILCITY_TRANSLATION_URL', plugin_dir_url(__FILE__));
define('WILCITY_TRANSLATION_JS_URL', plugin_dir_url(__FILE__) . 'admin/js/');

new \WilcityTranslation\Controllers\TranslationController();
