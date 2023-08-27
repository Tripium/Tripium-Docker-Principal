<?php


namespace WilokeNotificationBar\Controllers;


class DisplayBannerToFrontEndController extends Controller
{
	public function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'bannerFrontEnd']);
	}

	public function bannerFrontEnd()
	{
		$aGetOptionBanner = get_option($this->optionKey);
		if (isset($aGetOptionBanner['generalSettings']) && $aGetOptionBanner['generalSettings']['bannerStatus']
			&& isset($aGetOptionBanner['slideItemSettings']) && !empty($aGetOptionBanner['slideItemSettings'])) {
			$aBannerFrontEnd['generalSettings'] = $aGetOptionBanner['generalSettings'];
			$aBannerFrontEnd['items'] = array_values($aGetOptionBanner['slideItemSettings']);
      
			wp_enqueue_style(
				'wiloke-nb-enqueue-style-main',
				WILOKE_NB_DIR_URL . "assets/banner-front-end/main.css",
				[],
				WILOKE_NB_VERSION
			);

			wp_register_script(
				'wiloke-nb-enqueue-script-body-open',
				WILOKE_NB_DIR_URL . "assets/banner-front-end/body-open.js",
				[],
				WILOKE_NB_VERSION,
				true
			);

			wp_localize_script(
				'wiloke-nb-enqueue-script-body-open',
				$this->bannerFrontEnd,
				$aBannerFrontEnd
			);

			wp_enqueue_script(
				'wiloke-nb-enqueue-script-main',
				WILOKE_NB_DIR_URL . "assets/banner-front-end/main.js",
				['wiloke-nb-enqueue-script-body-open'],
				WILOKE_NB_VERSION,
				true
			);
		}
	}
}
