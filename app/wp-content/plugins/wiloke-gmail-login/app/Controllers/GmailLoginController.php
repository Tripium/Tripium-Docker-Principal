<?php

namespace WilokeGmailLogin\Controllers;

use Google\Service\Oauth2;
use Google_Client;
use Google_Service_Oauth2;
use WilokeListingTools\Framework\Helpers\SetSettings;
use Google\Exception;
use WILCITY_APP\Helpers\App;
use WilokeGmailLogin\Helpers\Option;
use WP_User;

/**
 * Class GmailLoginController
 * @package WilokeGmailLogin\Controllers
 */
class GmailLoginController
{
	/**
	 * @var $oClient Google_Client
	 */
	private $oClient;

	/**
	 * @var array
	 */
	private $aRequires
		= [
			'client_id',
			'client_secret',
			'redirect_uri'
		];

	/**
	 * GmailLoginController constructor.
	 */
	public function __construct()
	{
		add_filter(
			'wiloke-gmail-login/filter/app/Controllers/GmailLoginController/getLoginUrl',
			[$this, 'getLoginUrl'],
			10
		);

		add_filter(
			'wiloke-gmail-login/filter/app/Controllers/GmailLoginController/verifyGmailLogin',
			[$this, 'verifyGmailLogin'],
			10,
			2
		);
		add_filter(
			'wiloke-gmail-login/filter/app/Controllers/GmailLoginController/verifyGmailLoginAPI',
			[$this, 'verifyGmailLoginAPI'],
			11,
			2
		);
	}

	/**
	 * @return Google_Client
	 */
	public function getGoogleAPI(): Google_Client
	{
		if (!empty($this->oClient)) {
			return $this->oClient;
		}

		$this->oClient = new Google_Client();

		$this->oClient->setClientId(Option::getField('client_id'));
		$this->oClient->setClientSecret(Option::getField('client_secret'));
		$this->oClient->setRedirectUri(Option::getField('redirect_uri'));
		$this->oClient->addScope('email');
		$this->oClient->addScope('profile');
		return $this->oClient;
	}

	/**
	 * @param $aResponse
	 * @param $code
	 * @return array
	 */
	public function verifyGmailLoginAPI($aResponse, $code): array
	{
		try {
			$aValidation = $this->validate();
			if ($aValidation['status'] == 'error') {
				return $aValidation;
			}

			$this->getGoogleAPI();
			$accessToken = $code;
			if (version_compare(Google_Client::LIBVER, '2.2.3', '>=')) {
				$this->oClient->setAccessToken([
					'access_token' => $accessToken,
					'expires_in'   => strtotime('+2 days')
				]);
			} else {
				$this->oClient->setAccessToken($accessToken);
			}
			[$userID, $oUser] = $this->extracted();

			$oUser = new WP_User($userID);
			$aTokenResponse = apply_filters(
				'wilcity/filter/wiloke-mobile-app/createPermanentToken',
				[
					'status' => 'error',
					'msg'    => 'Please active Wilcity Mobile App'
				],
				$oUser
			);

			if ($aTokenResponse['status'] == 'error') {
				return $aTokenResponse;
			}

			if (function_exists('wilcityAppGetLanguageFiles')) {
				return App::get('UserInfo')->buildUserInfo($oUser, $aTokenResponse['token']);
			}

			return [
				'status' => 'success',
				'msg'    => __('The process is valid', 'wiloke-gmail-id'),
				'token'  => $aTokenResponse['token']
			];
		}
		catch (\Exception $e) {
			$aError = json_decode($e->getMessage(), true);
			if (is_array($aError) && isset($aError['error'])) {
				return [
					'status' => 'error',
					'msg'    => $aError['error']['message']
				];
			}
			return [
				'status' => 'error',
				'msg'    => $e->getMessage()
			];
		}
	}

	/**
	 * @param $aResponse
	 * @return array
	 */
	public function verifyGmailLogin($aResponse, $code)
	{
		$aValidation = $this->validate();
		if ($aValidation['status'] == 'error') {
			return $aValidation;
		}

		try {
			$this->getGoogleAPI();
			$aToken = $this->oClient->fetchAccessTokenWithAuthCode($code);
			$accessToken = $aToken['access_token'];
			$this->oClient->setAccessToken([
				'access_token' => $accessToken,
				'expires_in'   => strtotime('+2 days')
			]);
			[$userID, $oUser] = $this->extracted();
		}
		catch (\Exception $e) {
			return [
				'status' => 'error',
				'msg'    => $e->getMessage()
			];
		}

		return [
			'status' => 'success',
			'msg'    => __('The process is valid', 'wiloke-gmail-id'),
			'data'   => [
				'userID' => $userID
			]
		];
	}

	/**
	 * @param $aResponse
	 * @return array
	 */
	public function getLoginUrl($aResponse): array
	{
		try {
			$aResponse = [
				'status' => 'success',
				'data'   => [
					'redirect_uri' => $this->getGoogleAPI()->createAuthUrl()
				]
			];
		}
		catch (\Exception $oException) {
			$aResponse = [
				'status' => 'error',
				'msg'    => $oException->getMessage()
			];
		}

		return $aResponse;
	}

	/**
	 * @return array
	 */
	public function validate()
	{
		foreach ($this->aRequires as $key) {
			$config = Option::getField($key);
			if (empty($config)) {
				return [
					'status' => 'error',
					'msg'    => sprintf(esc_html__('The %s is required', 'wiloke-gmail-login'), $key)
				];
			}
		}

		return [
			'status' => 'success',
			'msg'    => esc_html__('The data have been validated', 'wiloke-gmail-login')
		];
	}

	/**
	 * @param $userID
	 * @param $sMetaKey
	 * @return bool|string|null
	 */
	private function getUserByGmailID($sMetaKey, $userID)
	{
		global $wpdb;

		$userID = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s",
				$sMetaKey,
				$userID
			)
		);

		return empty($userID) ? false : $userID;
	}

	/**
	 * @param $email
	 * @return bool|WP_User
	 */
	public function getUserByEmail($email)
	{
		return get_user_by('email', $email);
	}

	/**
	 * @param $userID
	 * @param $sMetaKey
	 * @param $sMetaValue
	 */
	public function setUserGmailID($userID, $sMetaKey, $sMetaValue)
	{
		update_user_meta($userID, $sMetaKey, $sMetaValue);
	}

	/**
	 * @param $givenName
	 * @param $email
	 * @return string
	 */
	public function generateUserName($givenName, $email)
	{
		return $givenName . substr(md5($email . current_time('timestamp')), 1, 5);
	}

	/**
	 * @param $userName
	 * @param $userEmail
	 * @return array|int|\WP_Error
	 */
	protected function createAccount($userName, $userEmail)
	{
		$password = wp_generate_password($length = 10, true);
		$userID = wp_create_user($userName, $password, $userEmail);
		if (is_wp_error($userID)) {
			return [
				'status' => 'error',
				'msg'    => $userID->get_error_message(),
			];
		}
		SetSettings::setUserMeta($userID, 'confirmed', true);
		return $userID;
	}

	/**
	 * @return array
	 */
	public function extracted(): array
	{
		if (class_exists("Google_Service_Oauth2")) {
			$googleAuth = new Google_Service_Oauth2($this->oClient);
		} else {
			$googleAuth = new Oauth2($this->oClient);
		}

		$accountInfo = $googleAuth->userinfo->get();
		$userID = $this->getUserByGmailID('wiloke-gmail-id', $accountInfo->id);
		$oUser = null;
		if (empty($userID)) {
			$oUser = $this->getUserByEmail($accountInfo->email);
			if ($oUser) {
				$userID = $oUser->ID;
			}
		}
		if (!empty($userID)) {
			$this->setUserGmailID($userID, 'wiloke-gmail-id', $accountInfo->id);

		} else {
			$userName = $this->generateUserName($accountInfo->givenName, $accountInfo->email);
			$userID = $this->createAccount($userName, $accountInfo->email);
			if ($userID) {
				$this->setUserGmailID($userID, 'wiloke-gmail-id', $accountInfo->id);
			}
		}
		return [$userID, $oUser];
	}
}
