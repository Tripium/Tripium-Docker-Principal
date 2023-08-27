<?php


namespace WilcityAddListingOnApp\Controllers;


use WilcityServiceClient\Helpers\PremiumPlugin;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Frontend\User;

class DashboardController
{
	public function __construct()
	{
		add_filter('wilcity/wilcity-mobile-app/dashboard-navigator', [$this, 'addAddListingToDashboard'], 10, 2);
	}

	public function addAddListingToDashboard($aItems, $userId): array
	{
		if (PremiumPlugin::isExpired('wilcity-app-addlisting')) {
			return $aItems;
		}

		if (GetWilokeSubmission::getField('toggle') !== 'enable') {
			return $aItems;
		}

		if (User::canSubmitListing($userId)) {
			$aItem = [
				'name'     => esc_html__('Add Listing', 'wilcity'),
				'icon'     => 'la la-edit',
				'endpoint' => GetWilokeSubmission::getField('package', true)
			];
		} else {
			if (GetWilokeSubmission::getField('toggle_become_an_author') == 'enable') {
				$aItem = [
					'name'     => esc_html__('Become an author', 'wilcity'),
					'icon'     => 'la la-edit',
					'endpoint' => GetWilokeSubmission::getField('become_an_author_page', true)
				];
			}
		}

		if (!isset($aItem)) {
			return $aItems;
		}

		$aItems = array_merge(
			[
				$aItem
			],
			$aItems
		);

		return $aItems;
	}
}
