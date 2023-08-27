<?php

namespace WilokeGmailLogin\Controllers;

use WilokeGmailLogin\Helpers\Option;

/**
 * Class AdminMenuController
 * @package WilokeGmailLogin\Controllers
 */
class AdminMenuController
{
	/**
	 * @var string
	 */
	public $prefix = 'wiloke_gmail_login_';
	/**
	 * @var string
	 */
	public $slug = 'wiloke-google-api-setting';
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
			'Wiloke Google API Setting',
			'Wiloke Google API Setting',
			'administrator',
			$this->slug,
			[$this, 'settings']
		);
	}

	public function settings()
	{
		$this->saveOption();
		$this->aOptions = Option::getAll();

		include WILOKE_GMAIL_VIEWS . 'wiloke-google-api-settings.php';
	}

	public function saveOption()
	{
		$aValues = [];
		if (isset($_POST['wiloke-google-api-field']) && !empty($_POST['wiloke-google-api-field'])) {
			if (wp_verify_nonce($_POST['wiloke-google-api-field'], 'wiloke-google-api-action')) {
				if (isset($_POST['wiloke-google']) && !empty($_POST['wiloke-google'])) {
					foreach ($_POST['wiloke-google'] as $key => $val) {
						$aValues[sanitize_text_field($key)] = sanitize_text_field(trim($val));
					}
				}
				Option::saveGoogleAPISettings($aValues);
			}
		}
	}
}
