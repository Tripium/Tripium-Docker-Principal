<?php


namespace WilcityHsBlog\Controllers;


class ThemeOptionsController
{
	public function __construct()
	{
		add_filter('wilcity/theme-options/configurations', [$this, 'addThemeOptions']);
	}

	public function addThemeOptions($aOptions)
	{
		$aThemeOptions = include WILCITY_HSBLOG_DIR . 'configs/theme-options.php';
		return array_merge($aOptions, $aThemeOptions);
	}
}
