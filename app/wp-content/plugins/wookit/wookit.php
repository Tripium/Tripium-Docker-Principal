<?php
/**
 * Plugin Name: WooKit
 * Plugin URI: https://wookit.myshopkit.app
 * Description: The one kit to boost sales
 * Version: 1.0.3
 * Author: Wiloke
 * Author URI: https://wiloke.com
 * Text Domain: wookit
 */

add_action('admin_notices', function () {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        ?>
        <div class="notice notice-error" style="padding: 20px; border-left:  4px solid #dc3232; color: red;">
            <?php esc_html_e('In order to use WooKit plugin, you need to upgrade PHP version to 7.4. Please contact your hosting 
            provider to report this issue.', 'wookit'); ?>
        </div>
        <?php
        return false;
    }

    if (!function_exists('locale_get_region')) {
        ?>
        <div class="notice notice-error" style="padding: 20px; border-left:  4px solid #dc3232; color: red;">
            <?php esc_html_e('Missing PHP intl extension. Please contact your hosting provider to report this issue',
                'wookit'); ?>
        </div>
        <?php
        return false;
    }
});


define('WOOKIT_VERSION', time());
define('WOOKIT_NAMESPACE', 'wookit');
define('WOOKIT_HOOK_PREFIX', 'wookit/');
define('WOOKIT_PREFIX', 'wookit_');
define('WOOKIT_REST_VERSION', 'v1');
define('WOOKIT_REST_BASE', WOOKIT_NAMESPACE . '/' . WOOKIT_REST_VERSION);
define('WOOKIT_REST_NAMESPACE', 'wookit');
define('WOOKIT_DS', '/');

define('WOOKIT_REST', WOOKIT_REST_NAMESPACE . WOOKIT_DS . WOOKIT_REST_VERSION);
define('WOOKIT_URL', plugin_dir_url(__FILE__));
define('WOOKIT_PATH', plugin_dir_path(__FILE__));


use WooKit\Dashboard\Controllers\AuthController;
use WooKit\MailServices;
use WooKit\Shared\App;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

//Tao file ConfigTemplate
App::bind('TemplateMeta', require_once(WOOKIT_PATH . 'src/Shared/Configs/TemplateMeta.php'));


require_once(WOOKIT_PATH . 'src/MailServices/MailServices.php');
require_once(WOOKIT_PATH . 'src/Insight/Insight.php');
require_once(WOOKIT_PATH . 'src/SmartBar/smartbar.php');
require_once(WOOKIT_PATH . 'src/Popup/popup.php');
require_once(WOOKIT_PATH . 'src/Discount/Discount.php');
require_once(WOOKIT_PATH . 'src/Dashboard/Dashboard.php');
require_once(WOOKIT_PATH . 'src/Product/Product.php');
require_once(WOOKIT_PATH . 'src/Page/Page.php');
require_once(WOOKIT_PATH . 'src/General/General.php');
require_once(WOOKIT_PATH . 'src/PostScript/PostScript.php');
require_once(WOOKIT_PATH . 'src/Images/Images.php');
require_once(WOOKIT_PATH . 'src/Slidein/Slidein.php');

register_activation_hook(__FILE__, function () {
    AuthController::generateAuth();
});

register_deactivation_hook(__FILE__, function () {
    AuthController::autoDeleteAuth();
});

add_filter('wp_is_application_passwords_available', '__return_true', 9999);

add_action('plugins_loaded', 'wookitAfterPluginsLoaded');
function wookitAfterPluginsLoaded()
{
    load_plugin_textdomain('wookit', false, basename(dirname(__DIR__)) . '/languages');
}
