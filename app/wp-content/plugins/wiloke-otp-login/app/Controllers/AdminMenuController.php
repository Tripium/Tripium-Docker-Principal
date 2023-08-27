<?php

namespace WilokeOTPLogin\Controllers;

use WilokeOTPLogin\Helpers\Option;

/**
 * Class AdminMenuController
 * @package WilokeOTPLogin\Controllers
 */
class AdminMenuController
{
	/**
	 * @var string
	 */
	public $prefix = 'wilokeotplogin_';
	/**
	 * @var string
	 */
	public $slug = 'wiloke-otp-login';
	/**
	 * @var
	 */
	public $aOptions;

	/**
	 * OTPLoginController constructor.
	 */
	public function __construct()
	{
		add_action('admin_menu', [$this, 'registerMenu']);
	}

	public function registerMenu()
	{
		add_menu_page(
			'Wiloke OTP Login',
			'Wiloke OTP Login',
			'administrator',
			$this->slug,
			[$this, 'settings'],
			'dashicons-admin-network'
		);
	}

	public function settings()
	{
		$this->saveOption();
		$this->aOptions = Option::getOTPLoginSettings();

		include WILOKE_OTP_VIEWS . 'wiloke-otp-login-settings.php';
	}

	public function saveOption()
	{
		$aValues = [];
		if (isset($_POST['wiloke-otp-field']) && !empty($_POST['wiloke-otp-field'])) {
			if (wp_verify_nonce($_POST['wiloke-otp-field'], 'wiloke-otp-action')) {
				if (isset($_POST['wilokeotp']) && !empty($_POST['wilokeotp'])) {
					foreach ($_POST['wilokeotp'] as $key => $val) {
						$aValues[sanitize_text_field($key)] = sanitize_text_field(trim($val));
					}
				}
				Option::saveOTPLoginSettings($aValues);
			}
		}
	}

	/**
	 * @return string|void
	 */
	public function getEmailContent()
	{
		if (empty($this->aOptions['email_content'])) {
			return __('Hi, your OTP code is %OTPcode%', 'wiloke-otp-login');
		}

		return $this->aOptions['email_content'];
	}
}
