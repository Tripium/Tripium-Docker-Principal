<?php

namespace MyshopKitDesignWizard\Dashboard\Shared;


use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use WilcityServiceClient\Helpers\GetSettings;

trait GeneralHelper
{
    protected string $dashboardSlug = 'dashboard';
    protected string $authSlug      = 'auth-settings';

    protected function getDashboardSlug(): string
    {
        return AutoPrefix::namePrefix($this->dashboardSlug);
    }

    protected function getAuthSlug(): string
    {
        return AutoPrefix::namePrefix($this->authSlug);
    }

    public function getToken(): string
    {
		return base64_encode(Option::getUsername() . ':' . Option::getApplicationPassword());
    }
	public function getTokenIframe(): string
	{
		return base64_encode(get_bloginfo('admin_email') . ',' .md5(get_bloginfo('admin_email')));
	}
}
