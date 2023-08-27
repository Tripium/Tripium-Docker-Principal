<?php

namespace WooKit\Dashboard\Controllers;

use Exception;
use WooKit\Dashboard\Shared\GeneralHelper;
use WooKit\Dashboard\Shared\Option;
use WooKit\Illuminate\Message\MessageFactory;
use WP_Application_Passwords;
use WP_REST_Request;
use WP_User;

class AuthController
{
	use GeneralHelper;

	const WP_AJAX_GET_CODE_APP_PASS        = 'wp_ajax_' . WOOKIT_PREFIX . 'getCodeAuth';
	const WP_AJAX_NOPRIV_GET_CODE_APP_PASS = 'wp_ajax_nopriv_' . WOOKIT_PREFIX . 'getCodeAuth';
	const WP_AJAX_REVOKE_PURCHASE_CODE     = 'wp_ajax_' . WOOKIT_PREFIX . 'revokePurchaseCode';
	public array $aOptions = [];

	public function __construct()
	{
		add_action(self::WP_AJAX_GET_CODE_APP_PASS, [$this, 'getCodeAuth']);
		add_action(self::WP_AJAX_NOPRIV_GET_CODE_APP_PASS, [$this, 'getCodeAuth']);
		add_action(self::WP_AJAX_REVOKE_PURCHASE_CODE, [$this, 'revokePurchaseCode']);
		add_action('admin_menu', [$this, 'registerMenu']);
		add_action('rest_api_init', [$this, 'registerRouter']);
	}

	public static function autoDeleteAuth()
	{
		if (!current_user_can('administrator')) {
			return false;
		}

		if (!class_exists('WP_Application_Passwords')) {
			return false;
		}

		$aOptions = Option::getAuthSettings();
		if (!empty($aOptions['app_password'])) {
			WP_Application_Passwords::delete_application_password(get_current_user_id(), $aOptions['uuid']);
		}

		Option::deleteAuthSettings();
	}

	public static function generateAuth()
	{
		if (!current_user_can('administrator')) {
			return false;
		}

		if (!class_exists('WP_Application_Passwords')) {
			return false;
		}

		self::performGenerateAuth();
	}

	private static function performGenerateAuth()
	{
		$aOptions = Option::getAuthSettings();
		if (!empty($aOptions['app_password'])) {
			WP_Application_Passwords::delete_application_password(get_current_user_id(), $aOptions['uuid']);
		}

		$aResponse = WP_Application_Passwords::create_new_application_password(
			get_current_user_id(),
			[
				'name' => 'WooKit'
			]
		);

		if (!is_wp_error($aResponse)) {
			Option::saveAuthSettings([
				'username'     => (new WP_User(get_current_user_id()))->user_login,
				'app_password' => $aResponse[0],
				'uuid'         => $aResponse[1]['uuid']
			]);
		}
	}

	public function registerRouter()
	{
		register_rest_route(
			WOOKIT_REST,
			'auth',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'checkFieldsAuth'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(
			WOOKIT_REST,
			'purchase-code',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'savePurchaseCode'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'checkPurchaseCode'],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	public function checkPurchaseCode(WP_REST_Request $oRequest)
	{
		return MessageFactory::factory('rest')->success('success',
			[
				'hasPurchaseCode' => !empty($this->getToken())
			]);
	}

	public function savePurchaseCode(WP_REST_Request $oRequest)
	{
		if (!is_user_logged_in() || !current_user_can('administrator')) {
			return MessageFactory::factory('rest')->error('You must log into the site to use this feature', 403);
		}

		if (empty($oRequest->get_param('purchase_code'))) {
			return MessageFactory::factory('rest')->error('Purchase Code is required', 400);
		}

		update_option('wookit_purchase_code', $oRequest->get_param('purchase_code'));
		return MessageFactory::factory('rest')->success('Oke');
	}

	public function checkFieldsAuth(WP_REST_Request $oRequest)
	{
		$username = $oRequest->get_param('username');
		$appPassword = $oRequest->get_param('appPassword');
		try {
			if (empty($username)) {
				throw new Exception(esc_html__('Sorry, the username is required',
					'wookit'));
			}
			if (empty($appPassword)) {
				throw new Exception(esc_html__('Sorry, the application password is required',
					'wookit'));
			}

			$oUser = wp_authenticate_application_password(null, $username, $appPassword);
			if (empty($oUser) || is_wp_error($oUser)) {
				throw new Exception(esc_html__($oUser->get_error_message(),
					'wookit'), 400);
			}

			if (!in_array('administrator', $oUser->roles)) {
				throw new Exception(esc_html__('The application must belong to an Administrator account',
					'wookit'));
			}

			Option::saveAuthSettings([
				'username'     => $username,
				'app_password' => $appPassword,
			]);
			return MessageFactory::factory('rest')->success('Passed',
				[
					'hasPassed' => true
				]);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function renderSettings()
	{
		$this->saveOption();
		$this->aOptions = Option::getAuthSettings();

		include plugin_dir_path(__FILE__) . '../Views/AuthSettings.php';
	}

	public function saveOption()
	{
		$aValues = [];
		if (isset($_POST['wookit-auth-field']) && !empty($_POST['wookit-auth-field'])) {
			if (wp_verify_nonce($_POST['wookit-auth-field'], 'wookit-auth-action')) {
				if (isset($_POST['wookitAuth']) && !empty($_POST['wookitAuth'])) {
					foreach ($_POST['wookitAuth'] as $key => $val) {
						$aValues[sanitize_text_field($key)] = sanitize_text_field(trim($val));
					}
				}
				Option::saveAuthSettings($aValues);
			}
		}
	}

	public function getCodeAuth()
	{
		try {
			$username = Option::getUsername();
			$appPassword = Option::getApplicationPassword();

			if (empty($username) || empty($appPassword)) {
				throw new Exception(esc_html__('Please go to Users -> Profile -> Create a new Application password to complete this setting.',
					'wookit'), 400);
			}

			add_filter('application_password_is_api_request', '__return_true');
			$oAuthenticated = wp_authenticate_application_password(null, $username, $appPassword);

			if (!$oAuthenticated instanceof WP_User) {
				throw new Exception(
					esc_html__('Invalid Application Username or Token', 'wookit'), 400
				);
			}

			if (!in_array('administrator', $oAuthenticated->roles)) {
				throw new Exception(esc_html__('The application must belong to an Administrator account.',
					'wookit'), 400);
			}

			self::performGenerateAuth();

			return MessageFactory::factory('ajax')->success('Success', [
				'code' => base64_encode(Option::getUsername() . ':' . Option::getApplicationPassword())
			]);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('ajax')->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function revokePurchaseCode()
	{
		try {
			if (!is_user_logged_in() || !current_user_can('administrator')) {
				throw new Exception(esc_html__('The application must belong to an Administrator account.',
					'wookit'), 400);
			}
			$aResult = wp_remote_post('https://wookit.myshopkit.app/wp-json/ev/v1/verifications', [
					'method'      => 'DELETE',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => [
						'Content-Type: application/json'
					],
					'body'        => [
						'purchaseCode' => $_POST['purchaseCode'],
						'productName'  => 'wookit'
					]
				]
			);
			if (is_wp_error($aResult)) {
				throw new Exception($aResult->get_error_message(), $aResult->get_error_code());
			}
			$aResponse = json_decode(wp_remote_retrieve_body($aResult), true);
			if ($aResponse['status'] == 'error') {
				throw new Exception($aResponse['message'], $aResponse['code']);
			}
			update_option('wookit_purchase_code', '');
			return MessageFactory::factory('ajax')->success($aResponse['message'], $aResponse['code']);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('ajax')->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function registerMenu()
	{
		add_submenu_page(
			$this->getDashboardSlug(),
			esc_html__('Auth Settings', 'wookit'),
			esc_html__('Auth Settings', 'wookit'),
			'administrator',
			$this->getAuthSlug(),
			[$this, 'renderSettings']
		);
	}
}
