<?php

namespace WilcityAppDeepLink\Controllers;

class ThemeOptionsController
{
	public function __construct()
	{
		add_filter('wilcity/filter/wilcity-mobile-app/configs/themeoptions', [$this, 'addMobileSettings']);
	}

	public function addMobileSettings($aSettings)
	{
		$aFields = include WILCITY_APP_DEE_LINK_PATH . 'configs/themeoptions.php';
		$aSettings['fields'] = array_merge($aSettings['fields'], $aFields);

		return $aSettings;
	}
}
