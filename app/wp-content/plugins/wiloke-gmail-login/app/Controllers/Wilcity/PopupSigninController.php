<?php

namespace WilokeGmailLogin\Controllers\Wilcity;

use WilokeGmailLogin\Helpers\Option;
use WilokeListingTools\Framework\Store\Session;

class PopupSigninController
{
	public function __construct()
	{
		add_filter(
			'wilcity/wiloke-listing-tools/app/Controllers/UserController/printLoginConfiguration',
			[
				$this, 'maybeAddGmailLoginToSocialLogin'
			]
		);
		add_action('wp_ajax_nopriv_wilcity_gmail_sign_url', [$this, 'getSignInUrl']);
	}

	public function getSignInUrl()
	{
		$status = check_ajax_referer('wilSecurity', 'security', 0);

		if (!$status) {
			wp_send_json_error(['msg' => esc_html__('Invalid code', 'wiloke-gmail-login')]);
		}

		$aResponse = apply_filters('wiloke-gmail-login/filter/app/Controllers/GmailLoginController/getLoginUrl', []);

		if ($aResponse['status'] == 'error') {
			wp_send_json_error($aResponse);
		}

		Session::setSession('social_login', 'gmail-login');
		Session::setSession('redirect_to', $_POST['afterLoggedInRedirectTo']);
		wp_send_json_success($aResponse);
	}

	public function maybeAddGmailLoginToSocialLogin($aSocialNetworks)
	{
		if (Option::getField('enable') == 'no') {
			return $aSocialNetworks;
		}

		$aSocialNetworks[] = [
			'social' => 'gmail-login'
		];

		return $aSocialNetworks;
	}
}
