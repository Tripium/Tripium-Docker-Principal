<?php


namespace WilcityVR\Controllers;


use WilcityVR\Helpers\App;

class ListingPlanController
{
	public function __construct()
	{
		add_filter('wilcity/filter/wiloke-listing-tools/config/listing-plan/listing_plan_settings',
			[$this, 'addToggleVRToPlan']);
	}

	public function addToggleVRToPlan($aPlans)
	{
		$aPlans[] = App::get('listingplan');
		return $aPlans;
	}
}