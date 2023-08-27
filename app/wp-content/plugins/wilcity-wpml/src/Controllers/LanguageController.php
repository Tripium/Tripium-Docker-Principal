<?php
namespace WilcityWPMLAPP\Controllers;

use WilcityServiceClient\Helpers\PremiumPlugin;

class LanguageController
{
	public function __construct()
	{
		add_filter('wiloke-listing-tools/app/Framework/Helpers/WPML/getCurrentLanguageApp', [$this, 'filterLanguage']);
	}

	public function filterLanguage($lang)
	{
		if (PremiumPlugin::isExpired('wilcity-wpml')) {
			return false;
		}

		if (isset($_GET['lang']) && !empty($_GET['lang'])) {
			$lang = $_GET['lang'];
		} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
			parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $aQueries);
			if (isset($aQueries['lang'])) {
				$lang = $aQueries['lang'];
			}
		}

		return $lang;
	}
}
