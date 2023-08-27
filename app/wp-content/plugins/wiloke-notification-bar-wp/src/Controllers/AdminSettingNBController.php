<?php

namespace WilokeNotificationBar\Controllers;


use WilokeNotificationBar\Helpers\App;

class AdminSettingNBController extends Controller
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'registerMenu'));
        add_action('admin_enqueue_scripts', [$this, 'enqueueStylesAndJs']);
        add_action('wp_ajax_wiloke_save_notification_bar', [$this, 'SaveNotificationBar']);
    }

    public function registerMenu()
    {
        add_menu_page(
            esc_html__('Notification Bar', 'wiloke-notification-bar-wp'),
            esc_html__('Wiloke Notification Bar', 'wiloke-notification-bar-wp'),
            'administrator',
            $this->menuSlug,
            [$this, 'menuSetting'],
            'dashicons-controls-volumeon'
        );
    }

    public function menuSetting()
    {
        ?>
        <div id="root"></div>
        <div id="wilModals"></div>
        <?php

    }

	public function SaveNotificationBar()
	{
		if (isset($_POST) && !empty($_POST)) {
			update_option($this->optionKey, json_decode(stripslashes($_POST['data']), true));
			wp_send_json_success([
				'msg' => esc_html__('Data Update success', 'wiloke-notification-bar-wp')
			]);
		}
		wp_send_json_error(['msg' => esc_html__('Data Update error', 'wiloke-notification-bar-wp')]);
	}

	public function enqueueStylesAndJs()
	{
		$oCurrent = get_current_screen();
		if (strpos($oCurrent->base, $this->menuSlug) !== false) {
			foreach (glob(WILOKE_NB_DIR_PATH . 'assets/css/*.css') as $cssOrder) {
				$aAnalysisNameCSS = (explode('/', $cssOrder));
				$analysisNameCSS = end($aAnalysisNameCSS);
				$src = WILOKE_NB_DIR_URL . 'assets/css/' . $analysisNameCSS;
				wp_enqueue_style('wiloke-nb-admin-page-enqueue-style-' . $analysisNameCSS, $src, [], WILOKE_NB_VERSION,
					'all');
			}
			foreach (glob(WILOKE_NB_DIR_PATH . 'assets/js/*.js') as $jsOrder) {
				$aAnalysisNameJS = (explode('/', $jsOrder));
				$analysisNameJS = end($aAnalysisNameJS);
				$src = WILOKE_NB_DIR_URL . 'assets/js/' . $analysisNameJS;
				wp_register_script(
					'wiloke-nb-admin-page-enqueue-script-' . $analysisNameJS,
					$src, [], WILOKE_NB_VERSION, true
				);
				$endNameEnqueueJS = 'wiloke-nb-admin-page-enqueue-script-' . $analysisNameJS;
				$aNameEnqueueJS[] = 'wiloke-nb-admin-page-enqueue-script-' . $analysisNameJS;
			}
			wp_enqueue_media();
			wp_localize_script($endNameEnqueueJS, 'WilokeNBGlob', setupDataConfig($aHandling = \WilokeNotificationBar\Helpers\App::get('adminSetting')
				->handlingInSettings(),App::get('config/Settings')['aInitConfig']));
			foreach ($aNameEnqueueJS as $NameEnqueue) {
				wp_enqueue_script($NameEnqueue);
			}
		}
	}
}
