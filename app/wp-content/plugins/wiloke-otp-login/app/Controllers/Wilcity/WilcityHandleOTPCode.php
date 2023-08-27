<?php


namespace WilokeOTPLogin\Controllers\Wilcity;


use WILCITY_APP\Helpers\App;
use WilokeOTPLogin\Controllers\HandleController;

class WilcityHandleOTPCode
{
	public function __construct()
	{
		add_action('wp_ajax_nopriv_wilcity_send_otp_code', [$this, 'sendOTPCode']);
		add_action('wp_ajax_nopriv_wilcity_verify_otp_code', [$this, 'handleVerifyOTPCode']);

		add_action('rest_api_init', function () {
			register_rest_route(WILOKE_API_OTP . '/' . WILOKE_API_OTP_VERSION, 'login/otp/send', [
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'getOTPcode'],
					'permission_callback' => '__return_true'
				]
			]);
		});
		add_action('rest_api_init', function () {
			register_rest_route(WILOKE_API_OTP . '/' . WILOKE_API_OTP_VERSION, 'login/otp/verify', [
				'methods'  => 'POST',
				'callback' => [$this, 'handleAPIVerifyOTPCode'],
				'permission_callback' => '__return_true'
			]);
		});
	}

	public function getOTPcode(\WP_REST_Request $oRequest)
	{
		if (empty($oRequest->get_param('usernameOrEmail'))) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('The username is required', 'wilcity-mobile-app')
			];
		}
		$aResponse = apply_filters('wiloke-otp-login/filter/Controllers/HandleController/maybeSendOTP', [],
			$oRequest->get_param('usernameOrEmail'));
		if ($aResponse['status'] == 'error') {
			return $aResponse;
		}
		return $aResponse;
	}

	public function handleAPIVerifyOTPCode(\WP_REST_Request $oRequest)
	{
		if (empty($oRequest->get_param('otpCode'))) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('Missing OTP code', 'wilcity-otp-login')
			];
		}
		$aResponse = apply_filters(
			'wiloke-otp-login/filter/Controllers/HandleController/verifyOTPCode',
			[
				'status' => 'error'
			],
			$oRequest->get_param('otpCode')
		);
		if ($aResponse['status'] == 'error') {
			return $aResponse;
		}

		$oUser = new \WP_User($aResponse['userId']);
		$aTokenResponse = apply_filters(
			'wilcity/filter/wiloke-mobile-app/createPermanentToken',
			[
				'status' => 'error',
				'msg'    => esc_html__('Please active Wilcity Mobile App', 'wilcity-mobile-app')
			],
			$oUser
		);

		if ($aTokenResponse['status'] == 'error') {
			return $aTokenResponse;
		}
		return [
			"status" => "success",
			"msg"    => "loggedIn",
			"token"  => (App::get('UserInfo')->buildUserInfo($oUser, $aTokenResponse['token']))['token']
		];
	}

	public function sendOTPCode()
	{
		$aResponse = apply_filters('wiloke-otp-login/filter/Controllers/HandleController/maybeSendOTP', [],
			$_POST['email']);
		if ($aResponse['status'] == 'error') {
			wp_send_json_error($aResponse);
		}

		wp_send_json_success($aResponse);
	}


	public function handleVerifyOTPCode()
	{
		$aResponse = apply_filters(
			'wiloke-otp-login/filter/Controllers/HandleController/verifyOTPCode',
			[
				'status' => 'error'
			],
			$_POST['otpCode']
		);
		if ($aResponse['status'] == 'error') {
			wp_send_json_error($aResponse);
		}
		wp_set_auth_cookie($aResponse['userId'], true, is_ssl());

		$aResponse = apply_filters(
			'wilcity/wiloke-listing-tools/filter/logged-in/redirection',
			[],
			new \WP_User($aResponse['userId'])
		);

		wp_send_json_success($aResponse);
	}
}
