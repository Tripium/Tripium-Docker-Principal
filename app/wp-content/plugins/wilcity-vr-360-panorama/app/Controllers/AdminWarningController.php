<?php


namespace WilcityVR\Controllers;


use WilcityServiceClient\Helpers\PremiumPlugin;

class AdminWarningController
{
	public function __construct()
	{
		add_action('admin_notices', [$this, 'wicityServiceIsRequired']);
		add_action('admin_notices', [$this, 'invalidPurchaseCode']);
	}

	public function invalidPurchaseCode()
	{
		if (PremiumPlugin::isExpired('wilcity-vr-360-panorama')) {
			?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo PremiumPlugin::getExpiryMsg('wilcity-vr-360-panorama'); ?></p>
            </div>
			<?php
		}
	}

	public function wicityServiceIsRequired()
	{
		if (!defined('WILCITYSERIVCE_CLIENT_DIR')) {
			?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('In order to use Wilcity VR 360 Panorama, please active Wilcity Service plugin',
						'wilcity-vr-360-panorama'); ?></p>
            </div>
			<?php
		}
	}
}