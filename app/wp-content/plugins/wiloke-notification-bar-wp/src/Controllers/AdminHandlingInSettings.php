<?php


namespace WilokeNotificationBar\Controllers;


use WilokeNotificationBar\Helpers\App;

class AdminHandlingInSettings extends Controller
{
	public function handlingInSettings()
	{
		$aData = get_option($this->optionKey);
		if (isset($aData) && !empty($aData)) {
			if (!empty($aData['slideItemSettings'])) {
				foreach ($aData['slideItemSettings'] as $key => $aValues) {
					$aFields[$key] = updateValueField(App::get('config/Settings')['aInitConfig'], $aValues);
				}
			}else{
				$aFields = [];
			}
			$aCommonBanner = updateValueField(App::get('config/Settings')['aConfigCommon'], $aData['generalSettings']);
			$aCommonBannerCSS = updateValueField(App::get('config/Settings')['aConfigAddCss'],
				$aData['advancedSettings']);
		} else {
			$aFields = [];
			$aCommonBanner = App::get('config/Settings')['aConfigCommon'];
			$aCommonBannerCSS = App::get('config/Settings')['aConfigAddCss'];

		}
		return [
			'aFields'          => $aFields,
			'aCommonBanner'    => $aCommonBanner,
			'aCommonBannerCSS' => $aCommonBannerCSS,
			'aTranslations'    => App::get('config/Settings')['aTranslations']
		];
	}
}