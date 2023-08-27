<?php

namespace WilokeOTPLogin\Controllers\Wilcity;

use WilokeOTPLogin\Helpers\Option;

class PopupLoginController
{
	public function __construct()
	{
		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/RegisterLoginController/settings',
			[$this, 'maybeAddPopupLoginToFrontend']
		);
	}

	public function maybeAddPopupLoginToFrontend($aSettings)
	{
		if (!Option::isEnable()) {
			return $aSettings;
		}

		$aSettings['isOtpLogin'] = 'yes';

		return $aSettings;
	}
}
