<?php

namespace MyshopKitDesignWizard\Dashboard\Controllers;


use EBase\Shopify\LoginRegister\Models\CustomerShopModel;
use MyshopKitDesignWizard\Dashboard\Shared\GeneralHelper;
use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Shared\Option;

class DashboardController
{
	use GeneralHelper;

	const WILSM_GLOBAL = 'WILSM_GLOBAL';

	public function __construct()
	{
		add_action('admin_menu', [$this, 'registerMenu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueScriptsToDashboard']);
	}

	public static function getPageName()
	{
		return AutoPrefix::namePrefix('designWizardView');
	}

	public function enqueueScriptsToDashboard($hook): bool
	{
		$oUser = get_userdata(get_current_user_id());
		wp_localize_script('jquery', self::WILSM_GLOBAL, [
			'url'                             => admin_url('admin-ajax.php'),
			'restBase'                        => trailingslashit(rest_url(MYSHOPKIT_DW_REST)),
			'email'                           => get_option('admin_email'),
			'clientSite'                      => home_url('/'),
			'urlIframe'                       => add_query_arg([
				'page' => 'mskdw_designWizardView'
			], admin_url('admin.php')),
			'postID'                          => $_GET['post'] ?? 0,
			'pluginName'                      => esc_html__('Myshopkit Design Wizard', 'myshopkit-design-wizard'),
			'notionSuccessVerifyPurchaseCode' => esc_html__('Congrats, your purchase code has been updated successfully',
				'myshopkit-design-wizard'),
			'notionErrorVerifyPurchaseCode'   => esc_html__('The purchase code is required.',
				'myshopkit-design-wizard'),
			'token'                           => $this->getToken(),
			'tokens'                          => [
				'accessToken'  => get_option(AutoPrefix::namePrefix('accessToken')),
				'refreshToken' => get_option(AutoPrefix::namePrefix('refreshToken')),
				'isWordpress'  => true,
			],
			'tokenIframe'                     => $this->getTokenIframe(),
			'username'                        => $oUser->user_login,
			'roles'                           => ['subscriber'],
			'id'                              => $oUser->ID,
			'msg'                             => '',
			'status'                          => '',
			'isWordpress'                     => true,
			'Logo.png'                        => plugin_dir_url(__FILE__) . '../Static/images/Logo.png',
			'pro.png'                         => plugin_dir_url(__FILE__) . '../Static/images/pro.png',
			'rotateIcon.svg'                  => plugin_dir_url(__FILE__) . '../Static/images/rotateIcon.svg',
			'suggestions'                     => [],
			'avatar'                          => get_avatar_url($oUser->ID) ?? '',
			'redirectDashBoard'               => add_query_arg([
				'page' => 'mskdw_dashboard'
			], admin_url('admin.php'))
		]);
		wp_enqueue_script(
			AutoPrefix::namePrefix('dashboard-script'),
			plugin_dir_url(__FILE__) . '../Assets/Js/Script.js',
			['jquery'],
			MYSHOPKIT_DW_VERSION,
			true
		);
		wp_enqueue_style(
			AutoPrefix::namePrefix('dashboard-style'),
			plugin_dir_url(__FILE__) . '../Assets/Css/Style.css',
			[],
			MYSHOPKIT_DW_VERSION
		);
		if (preg_match('/mskdw_designWizardView/', $hook)) {
			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
				[],
				MYSHOPKIT_DW_VERSION
			);
			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/fonts/stylesheet.css',
				[],
				MYSHOPKIT_DW_VERSION
			);
			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				'https://fonts.googleapis.com/icon?family=Material+Icons',
				[],
				MYSHOPKIT_DW_VERSION
			);
			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				'https://fonts.googleapis.com/icon?family=Material+Icons+Outlined',
				[],
				MYSHOPKIT_DW_VERSION
			);
			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css',
				[],
				MYSHOPKIT_DW_VERSION
			);

			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/css/main.chunk.css',
				[],
				MYSHOPKIT_DW_VERSION
			);
			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/css/_vendors.chunk.css',
				[],
				MYSHOPKIT_DW_VERSION
			);

			wp_enqueue_style(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/icomoon-ultimate/icomoon-ultimate.css',
				[],
				MYSHOPKIT_DW_VERSION
			);

			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/runtime.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-lodash-es.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-lodash.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-antd.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-ant-design.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-wiloke-react-core.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-konva.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-react-color.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-rc-select.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-react-redux.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-rc-field-form.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-rc-menu.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-rc-slider.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-redux-saga.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-react-dom.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-react-reconciler.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-i18next.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-react-sortable-hoc.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-react-redux.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors/vendor-_vendors.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/vendors~main.chunk.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
			wp_enqueue_script(
				uniqid(MYSHOPKIT_DW_VERSION),
				plugin_dir_url(__FILE__) . '../Static/js/main.chunk.js',
				['jquery'],
				MYSHOPKIT_DW_VERSION,
				true
			);
		}
		return false;
	}

	public function registerMenu()
	{
		add_menu_page(
			esc_html__('Getting Started', 'myshopkit-design-wizard'),
			esc_html__('Getting Started', 'myshopkit-design-wizard'),
			'publish_posts',
			AutoPrefix::namePrefix('dashboard'),
			[$this, 'renderSettings'],
			'dashicons-cover-image'
		);
		add_submenu_page(
			AutoPrefix::namePrefix('dashboard') . '',
			esc_html__('Editor', 'myshopkit-design-wizard'),
			esc_html__('Editor', 'myshopkit-design-wizard'),
			'publish_posts',
			self::getPageName(),
			[$this, 'renderViewSettings']
		);
	}

	public function renderViewSettings()
	{
		if (get_option(AutoPrefix::namePrefix('verified'))) {
			?>
            <div id="root"></div>
			<?php
		} else {
            ?>
            <p>Your purchase code is not valid or expired. Click to <a href="https://wilcityservice.com/product/wilcity-plugin-bundles/" target="_blank">renew your purchase code</a>
            </p>
            <?php
		}
	}

	public function renderSettings()
	{
		return include plugin_dir_path(__FILE__) . '../Views/index.php';
	}
}
