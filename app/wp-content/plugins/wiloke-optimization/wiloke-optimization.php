<?php
/**
 * Plugin Name: Wiloke Optimization Beta
 * Description: Speed up Wiloke Theme
 * Author: Wiloke
 * Version: 1.0.8
 * Author URI: https://wiloke.com/
 * Plugin URI: https://wilcityservice.com
 */

define('WILOKE_OPTIMIZATION_PATH', plugin_dir_path(__FILE__));
define('WILOKE_OPTIMIZATION_VERSION', '1.0.8');
define('WILOKE_HOOK_PREFIX', 'wiloke-optimization/');

global $oAsyncCacheController;

use WilokeOptimization\Cloudflare\Controllers\CacheController;
use WilokeOptimization\Cloudflare\Database\LogTable;
use WilokeOptimization\Nginx\Controllers\CacheController as NginxCacheController;
use WilokeOptimization\Shared\Controllers\OptimizationController;
use WilokeOptimization\StaticCache\Controllers\AdminBarController;
use WilokeOptimization\StaticCache\Controllers\Promooland\RestAPIController;
use WilokeOptimization\StaticCache\Controllers\StaticPageCacheController;
use WilokeOptimization\StaticCache\Controllers\Wilcity\SearchCacheController as WilcitySearchCacheController;
use WilokeOptimization\StaticCache\Database\StaticFileTBL;


require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

register_activation_hook(__FILE__, function () {
	if (class_exists('WilokeListingTools\Framework\Helpers\General')) {
		LogTable::createTable();
		if (!wp_next_scheduled('wiloke_optimization_check_cache_daily')) {
			wp_schedule_event(time(), 'daily', 'wiloke_optimization_check_cache_daily');
		}

		StaticFileTBL::createTable();
	}
});

register_deactivation_hook(__FILE__, function () {
	wp_clear_scheduled_hook('wiloke_optimization_check_cache_daily');
});


add_action('admin_notices', function () {
	if (version_compare(phpversion(), '7.4', '<')) :
		?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Wiloke Optimization: You must upgrade your PHP Version to 7.4 or higher',
					'wiloke-optimization');
				?></p>
        </div>
	<?php
	endif;
});

if (version_compare(phpversion(), '7.4', '>=')) {
	new OptimizationController;
	new CacheController;
	new NginxCacheController;

	if (defined('WILOKE_LISTING_TOOL_VERSION')) {
		new WilcitySearchCacheController();
	}

	new StaticPageCacheController;

	if (defined('PROOMOLAND_I18')) {
		new RestAPIController;
	}
	new AdminBarController();
}
