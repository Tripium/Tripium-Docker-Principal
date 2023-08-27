<?php

namespace WooKit\Dashboard\Controllers;

use Automattic\WooCommerce\Blocks\RestApi;
use WooKit\Dashboard\Shared\GeneralHelper;
use WooKit\Shared\AutoPrefix;

class DashboardController
{
	use GeneralHelper;

	const WOOKIT_GLOBAL = 'WookitGLOBAL';
	private string $wookitEditor = 'https://wookit-editor.netlify.app';

	public function __construct()
	{
		add_action('admin_menu', [$this, 'registerMenu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueScriptsToDashboard']);
	}

	public function enqueueScriptsToDashboard($hook): bool
	{
		wp_localize_script('jquery', self::WOOKIT_GLOBAL, [
			'url'              => admin_url('admin-ajax.php'),
			'restBase'         => trailingslashit(rest_url(WOOKIT_REST_BASE)),
			'email'            => get_option('admin_email'),
			'clientSite'       => home_url('/'),
			'purchaseCode'     => $this->getToken(),
			'purchaseCodeLink' => 'https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code',
			'tidio'            => 'bdzedo8yftsclnwmwmbcqcsyscbk4rtl'
		]);

		if ((strpos($hook, $this->getDashboardSlug()) !== false) || (strpos($hook, $this->getAuthSlug()) !== false)) {
			// enqueue script
			wp_enqueue_script(
				AutoPrefix::namePrefix('dashboard-script'),
				plugin_dir_url(__FILE__) . '../Assets/Js/Script.js',
				['jquery'],
				WOOKIT_VERSION,
				true
			);


			wp_enqueue_style(
				AutoPrefix::namePrefix('dashboard-style'),
				plugin_dir_url(__FILE__) . '../Assets/Css/Style.css',
				[],
				WOOKIT_VERSION,
				'media'
			);
		}
		return false;
	}

	public function registerMenu()
	{
		add_menu_page(
			esc_html__('WooKit Dashboard', 'wookit'),
			esc_html__('WooKit Dashboard', 'wookit'),
			'administrator',
			$this->getDashboardSlug(),
			[$this, 'renderSettings'],
			'dashicons-admin-network'
		);
	}

	public function renderSettings()
	{
		?>
        <div id="wookit-dashboard">
            <iframe id="shopkit-iframe" src="<?php echo esc_url($this->getIframe()); ?>" width="1500"
                    height="750"></iframe>
        </div>
		<?php
	}

	private function getIframe(): string
	{
		return defined('WOOKIT_IFRAME') ? WOOKIT_IFRAME : 'https://wookit.netlify.app/';
	}
}
