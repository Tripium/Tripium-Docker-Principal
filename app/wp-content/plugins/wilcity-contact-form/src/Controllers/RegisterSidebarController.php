<?php

namespace WilcityContactForm\Controllers;

use WilcityServiceClient\Helpers\PremiumPlugin;

/**
 * Class RegisterSidebarController
 * @package WilcityContactForm\Controllers
 */
class RegisterSidebarController
{
	public function __construct()
	{
		add_filter('wilcity/wiloke-listing-tools/filter/sidebar-items', [$this, 'registerSidebarItems']);
		add_filter('wilcity/wiloke-listing-tools/filter/sidebar-fields', [$this, 'addFieldSettingsToSidebar']);
	}

	public function addFieldSettingsToSidebar($aFields)
	{
//		if (!PremiumPlugin::isPlanAlive('wilcity-contact-form')) {
//			return $aFields;
//		}

		$aSidebars = wilcityContactFormRepository()
			->setFile('sidebar')
			->get('fields', true)
			->get('sections');

		$aFields['sections'] = $aFields['sections'] + $aSidebars;

		return $aFields;
	}

	public function registerSidebarItems($aSections)
	{
//		if (!PremiumPlugin::isPlanAlive('wilcity-contact-form')) {
//			return $aSections;
//		}

		$aSidebars = wilcityContactFormRepository()->setFile('sidebar')->get('sections');

		return $aSections + $aSidebars;
	}
}
