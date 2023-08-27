<?php


namespace WilokeGmailLogin\Controllers\Wilcity;


use WilokeListingTools\Framework\Store\Session;
use WP_REST_Request;

class GmailVerificationController
{
	public function __construct()
	{
		add_action('init', [$this, 'verifyGmailCode']);
		add_action('rest_api_init', function () {
			register_rest_route(WILOKE_API_GMAIL . '/' . WILOKE_GMAIL_VERSION, 'google/signIn', [
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'handleAPIVerifyGmailCode'],
					'permission_callback' => '__return_true'
				]
			]);
		});
	}

	public function handleAPIVerifyGmailCode(WP_REST_Request $oRequest)
	{
		$aResponse = apply_filters(
			'wiloke-gmail-login/filter/app/Controllers/GmailLoginController/verifyGmailLoginAPI',
			[],
			$oRequest->get_param('token')
		);

		if ($aResponse['status'] == 'error') {
			if (class_exists('WilokeListingTools\Framework\Store\Session')) {
				Session::addTopNotifications(
					'gmail-login',
					[
						'numberOfDisplays' => 1,
						'type'             => 'danger',
						'msg'              => $aResponse['msg']
					]
				);
			}
		}

		return $aResponse;
	}

	public function verifyGmailCode()
	{
		if (!class_exists('WilokeListingTools\Framework\Store\Session')) {
			return;
		}

		if (isset($_GET['code']) && strpos($_GET['scope'], 'googleapis') !== false) {
			$aResponse = apply_filters(
				'wiloke-gmail-login/filter/app/Controllers/GmailLoginController/verifyGmailLogin',
				[],
				$_REQUEST['code']
			);

			Session::destroySession('social_login');

			if ($aResponse['status'] != 'error') {
				$redirectTo = urldecode(Session::getSession('redirect_to', true));
				wp_set_auth_cookie($aResponse['data']['userID']);
				wp_safe_redirect($redirectTo);
				exit;
			}
		}
	}
}
