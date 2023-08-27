<?php

namespace WilcityContactForm\Controllers;

use WilokeListingTools\Framework\Helpers\GetSettings;

class SidebarOrderController
{
	public function __construct()
	{
		add_filter('wilcity/sidebar-order', [$this, 'maybeRemoveContactForm'], 10, 2);
		add_filter(
			'wilcity/filter/wilcity-mobile-app/app/Controllers/Listing/ListingSkeleton/getSCContent/contactForm7',
			[$this, 'renderSidebarContentToApp'],
			10,
			2
		);
	}

	public function renderSidebarContentToApp($val, $aSection)
	{
		if (empty($val)) {
			return '';
		}

		if (preg_match('/app-page-id="([0-9]+)"/', $val, $aMatch)) {
			if (isset($aMatch[1]) && get_post_status($aMatch[1]) == 'publish') {
				return add_query_arg(['iswebview' => 'yes'], get_permalink($aMatch[1]));
			}
		}

		return '';
	}

	public function maybeRemoveContactForm($aSidebarOrder, $post)
	{
		$planID = GetSettings::getListingBelongsToPlan($post->ID);
		$isRemoveContactForm = false;
		if (!empty($planID)) {
			$aPlanSettings = GetSettings::getPlanSettings($planID);
			$isRemoveContactForm = isset($aPlanSettings['toggle_contact_form']) &&
				$aPlanSettings['toggle_contact_form'] === 'disable';
		}

		if ($isRemoveContactForm || \WilokeThemeOptions::isEnable('contact_form_toggle', false)) {
			$aContactFormKeys = wilcityContactFormRepository()->setFile('sidebar')->get('sections');
			$aContactFormKeys = array_keys($aContactFormKeys);

			foreach ($aContactFormKeys as $key) {
				if (isset($aSidebarOrder[$key])) {
					unset($aSidebarOrder[$key]);
				}
			}
		}

		if (isset($aSidebarOrder['contactForm7'])) {
			$aSidebarOrder['contactForm7']['isWebview'] = "yes";
		}

		return $aSidebarOrder;
	}
}
