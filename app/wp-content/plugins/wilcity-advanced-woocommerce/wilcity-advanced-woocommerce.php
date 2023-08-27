<?php
/**
 * Plugin Name: Wilcity Advanced WooCommerce
 * Plugin URI: https://wilcity.com
 * Author: Wilcity
 * Author URI: https://wilcity.com
 * Version: 1.1.4
 * Description: An Advanced WooCommerce plugin for Wilcity
 * Text Domain: wilcity-advanced-woocommerce
 * Domain Path: /languages/
 */

use WilcityAdvancedProducts\Controllers\AddListingController;
use WilcityAdvancedProducts\Controllers\AdminController;
use WilcityAdvancedProducts\Controllers\ListingAdvancedProductSkeleton;
use WilcityAdvancedProducts\Controllers\SMSMessageController;
use WilcityAdvancedProducts\Controllers\NotificationController;
use WilcityAdvancedProducts\Controllers\CheckoutController;
use WilcityAdvancedProducts\Controllers\RegisterProductsNavigationController;
use WilcityAdvancedProducts\Controllers\PrintProductsController;
use WilcityAdvancedProducts\Helpers\ListingAdvancedProducts;
use WilokeRepository\Helpers\WilokeRepository;
use WilcityAdvancedProducts\Controllers\SingleNavigationController;
use WilcityAdvancedProducts\Controllers\AdminSettingController;
use WilcityAdvancedProducts\Controllers\ThemeOptionsController;
use WilcityAdvancedProducts\MetaBoxes\AdvancedProduct;
use WilokeListingTools\Framework\Helpers\App;

add_action('plugins_loaded', 'wilcityAdvancedWooCommerceLoadTextDomain');
function wilcityAdvancedWooCommerceLoadTextDomain()
{
	load_plugin_textdomain('wilcity-advanced-woocommerce', false, basename(dirname(__FILE__)) . '/languages');
}

function wilcityAdvancedWooCommerceGetFile()
{
	return WilokeRepository::init(plugin_dir_path(__FILE__) . 'configs/');
}

add_action('wiloke-listing-tools/run-extension', function () {
	define('WILCITY_ADVANCED_WOOCOMMERCE', plugin_dir_path(__FILE__));
	define('WILCITY_ADVANCED_WOOCOMMERCE_SMS_DIR', plugin_dir_path(__FILE__));

	if (!defined('WILCITY_MOBILE_APP_USING_TWILIO')) {
		define('WILCITY_MOBILE_APP_USING_TWILIO', true);
	}
	require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

	if (is_admin()) {
		App::bind('AdminController', new AdminController);
//            App::bind('AdminSettingController', new AdminSettingController);
		App::bind('ThemeOptionsController', new ThemeOptionsController);
	}

	App::bind('SingleNavigationController', new SingleNavigationController);
	App::bind('WilcityAdvancedWooCommerceAddListingController', new AddListingController);
	App::bind('SMSMessageController', new SMSMessageController);
	App::bind('NotificationController', new NotificationController);
	App::bind('CheckoutController', new CheckoutController);
	App::bind('RegisterProductsNavigationController', new RegisterProductsNavigationController);
	App::bind('PrintProductsController', new PrintProductsController);
	App::bind('AdvancedProduct', new AdvancedProduct);
	App::bind('ListingAdvancedProducts', new ListingAdvancedProducts);
	App::bind('ListingAdvancedProductSkeleton', new ListingAdvancedProductSkeleton);
});
