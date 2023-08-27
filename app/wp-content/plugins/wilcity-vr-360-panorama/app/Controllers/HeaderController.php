<?php


namespace WilcityVR\Controllers;


use WilcityServiceClient\Helpers\PremiumPlugin;
use  \WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\PlanHelper;

class HeaderController
{
	private $vrSrc;

	public function __construct()
	{
		add_filter('wilcity/filter/single-listing/header/type', [$this, 'maybeAddVrType'], 10, 2);
		add_action('wilcity/single-listing/header/vr360', [$this, 'printVRToHeader']);
	}

	public function printVRToHeader()
	{
		include WILCITY_VR_VIEWS_PATH . 'header.php';
	}

	public function maybeAddVrType($type, $post)
	{
		if (!PremiumPlugin::isExpired('wilcity-vr-360-panorama')) {
			$planID = GetSettings::getPostMeta($post->ID, 'belongs_to');
			$isAllowed = true;

			if (!empty($planID)) {
				$isAllowed = PlanHelper::isEnable($planID, 'toggle_vr_src');
			}

			if ($isAllowed) {
				$vrSrc = GetSettings::getPostMeta($post->ID, 'vr_src');
				if (!empty($vrSrc)) {
					$this->vrSrc = $vrSrc;
					return 'vr360';
				}
			}
		}

		return $type;
	}
}