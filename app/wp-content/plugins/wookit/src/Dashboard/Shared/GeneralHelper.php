<?php

namespace WooKit\Dashboard\Shared;

use WooKit\Shared\AutoPrefix;

trait GeneralHelper
{
    protected string $dashboardSlug = 'dashboard';
    protected string $authSlug = 'auth-settings';

    protected function getDashboardSlug(): string
    {
        return AutoPrefix::namePrefix($this->dashboardSlug);
    }

    protected function getAuthSlug(): string
    {
        return AutoPrefix::namePrefix($this->authSlug);
    }

	private function getToken()
	{
		$token = get_option('wookit_purchase_code');
		if (!empty($token)) {
			return $token;
		}

		if (class_exists('\WilcityServiceClient\Helpers\GetSettings')) {
			return \WilcityServiceClient\Helpers\GetSettings::getOptionField('secret_token');
		}
		return '';
	}
}
