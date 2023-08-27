<?php
/**
 * Plugin Name: MyShopkit Design Wizard
 * Plugin URI: https://wiloke.com
 * Author: wiloke
 * Author URI: https://wiloke.com
 * Version: 1.0
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Text-Domain: myshopkit-design-wizard
 */


define('MYSHOPKIT_DW_VERSION', uniqid());
define('MYSHOPKIT_DW_HOOK_PREFIX', ' myShopkitDesignWizard/');
define('MYSHOPKIT_DW_PREFIX', 'mskdw_');
define('MYSHOPKIT_DW_REST_VERSION', 'v1');
define('MYSHOPKIT_DW_REST_NAMESPACE', 'myshopkit-design-wizard');
define('MYSHOPKIT_DW_REST', MYSHOPKIT_DW_REST_NAMESPACE . '/' . MYSHOPKIT_DW_REST_VERSION);
define('MYSHOPKIT_DW_URL', plugin_dir_url(__FILE__));
define('MYSHOPKIT_DW_PATH', plugin_dir_path(__FILE__));
define('MYSHOPKIT_DESIGN_WIZARD_PLUGIN_URL', 'https://codecanyon.net/item/myshopkit-design-wizard/38194618');

add_action('plugins_loaded', 'myShopkitDesignWizardLoadPluginDomain');
if (!function_exists('myShopkitDesignWizardLoadPluginDomain')) {
	function myShopkitDesignWizardLoadPluginDomain()
	{
		load_plugin_textdomain('myshopkit-design-wizard', false, plugin_dir_path(__FILE__) . 'languages');
	}
}

add_action('admin_notices', 'myshopkitDesignWizardNotice');
function myshopkitDesignWizardNotice()
{
	if (!defined('WILCITYSERIVCE_VERSION')) {
		?>
        <div id="wilsm-converter-warning" class="notice notice-error sf-notice-nux is-dismissible">
            <div>
                <p>
					<?php esc_html_e(
						'In order to use MyShopKit Design Wizard, You have to activate Wilcity Service plugin.',
						'myshopkit-design-wizard'
					);
					?>
                </p>
            </div>
            <a href="https://wilcityservice.com/wilcity-service/" target="_blank">
				<?php esc_html_e('Installing Wilcity Service now', 'myshopkit-design-wizard'); ?>
            </a>
        </div>
		<?php
	}
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'src/Dashboard/Dashboard.php';
require_once plugin_dir_path(__FILE__) . 'src/Images/Images.php';
require_once plugin_dir_path(__FILE__) . 'src/Projects/Projects.php';
require_once plugin_dir_path(__FILE__) . 'src/Sidebar/Sidebar.php';
