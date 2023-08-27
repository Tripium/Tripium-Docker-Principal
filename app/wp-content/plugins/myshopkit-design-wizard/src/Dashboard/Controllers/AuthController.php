<?php

namespace MyshopKitDesignWizard\Dashboard\Controllers;

use EBase\Shopify\LoginRegister\Models\CustomerShopModel;
use Exception;
use MyshopKitDesignWizard\Dashboard\Shared\GeneralHelper;
use MyshopKitDesignWizard\Dashboard\Shared\Option;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Shared\TraitPageDetector;
use WP_Application_Passwords;
use WP_REST_Request;
use WP_User;
use WilcityServiceClient\Helpers\GetSettings;

class AuthController
{
	use GeneralHelper;
	use TraitPageDetector;

	public array $aOptions = [];

	public function __construct()
	{
		add_action('activated_plugin', [$this, 'handleActivatedPlugin'], 10, 2);
		add_action('admin_init', [$this, 'verifyPurchaseCode']);
	}

	public function handleActivatedPlugin($plugin, $network_activation)
	{
		if ($plugin == 'myshopkit-design-wizard/myshopkit-design-wizard.php') {
			wp_redirect(add_query_arg([
				'page' => 'mskdw_dashboard'
			], admin_url('admin.php')));
			exit();
		}
	}

	public function getCodeAuth()
	{
		try {
			$username = Option::getUsername();
			$appPassword = Option::getApplicationPassword();

			add_filter('application_password_is_api_request', '__return_true');
			if (empty($username) || empty($appPassword)) {
				$aResponse = WP_Application_Passwords::create_new_application_password(
					get_current_user_id(),
					[
						'name' => 'WilSM'
					]
				);

				if (!is_wp_error($aResponse)) {
					Option::saveAuthSettings([
						'username'     => (new WP_User(get_current_user_id()))->user_login,
						'app_password' => $aResponse[0],
						'uuid'         => $aResponse[1]['uuid']
					]);
				} else {
					Option::saveAuthSettings([
						'username'     => md5('user-local'),
						'app_password' => md5('pass-local'),
						'uuid'         => md5('uuid')
					]);
				}
				return base64_encode(Option::getUsername() . ':' . Option::getApplicationPassword());
			}
			return base64_encode(Option::getUsername() . ':' . Option::getApplicationPassword());
		}
		catch (Exception $exception) {
			return MessageFactory::factory('ajax')->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function verifyPurchaseCode()
	{
		if (!$this->isMyShopKitArea() || !class_exists('WilcityServiceClient\Helpers\GetSettings')) {
			return;
		}

		if (get_option(AutoPrefix::namePrefix('verified'))) {
			return;
		}

		try {
			$aResult = wp_remote_post('https://promooland.com/wp-json/ev/v1/verifications', [
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'blocking'    => true,
					'headers'     => [
						'Content-Type: application/json'
					],
					'body'        => [
						'productName'  => 'myshopkit-design-wizard',
						'clientSite'   => get_bloginfo('url'),
						'email'        => get_bloginfo('admin_email'),
						'purchaseCode' => GetSettings::getOptionField('secret_token')
					]
				]
			);

			if (is_wp_error($aResult)) {
				throw new Exception($aResult->get_error_message(), $aResult->get_error_code());
			}

			$aResponse = json_decode(wp_remote_retrieve_body($aResult), true);

			if ($aResponse['status'] == 'error') {
				throw new Exception($aResponse['error']['msg'], 401);
			}

			update_option(AutoPrefix::namePrefix('verified'), true);
			$this->registerUser();
			MessageFactory::factory('ajax')->success('Passed');
		}
		catch (Exception $exception) {
			MessageFactory::factory('ajax')->error(esc_html__('The purchase code does not exist',
				'myshopkit-design-wizard'), 401);
		}
	}

	public function registerUser(): bool
	{
		if (!$this->isMyShopKitArea() || !class_exists('WilcityServiceClient\Helpers\GetSettings')) {
			return false;
		}

		try {
			$aResult = wp_remote_post('https://promooland.com/wp-json/pl/v1/users', [
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'blocking'    => true,
					'headers'     => [
						'Content-Type: application/json'
					],
					'body'        => [
						'info' => [
							'name'        => get_bloginfo('name'),
							'description' => get_bloginfo('description'),
							'clientSite'  => get_bloginfo('url'),
							'email'       => get_bloginfo('admin_email'),
							'restBase'    => trailingslashit(rest_url(MYSHOPKIT_DW_REST)),
							'token'       => $this->getCodeAuth(),
							'purchase'    => GetSettings::getOptionField('secret_token')
						]
					]
				]
			);
			if (is_wp_error($aResult)) {
				throw new Exception($aResult->get_error_message(), $aResult->get_error_code());
			}

			$aResponse = json_decode(wp_remote_retrieve_body($aResult), true);
			if (isset($aResponse['error'])) {
				throw new Exception($aResponse['error']['msg'], 401);
			}
			update_option(AutoPrefix::namePrefix('accessToken'), $aResponse['accessToken']);
			update_option(AutoPrefix::namePrefix('refreshToken'), $aResponse['refreshToken']);
			if (isset($aResponse['isTest']) && !$aResponse['isTest']) {
				update_option(AutoPrefix::namePrefix('notion'), true);
			}
			return true;
		}
		catch (Exception $exception) {
			return true;
		}
	}
}
