<?php

namespace WilokeInstagramFeedhub\Controllers;

use WilokeInstagramFeedhub\Helpers\App;
use WilokeInstagramFeedhub\Helpers\InstafeedHub;
use WilokeInstagramFeedhub\Helpers\Option;
use WilokeListingTools\Controllers\Retrieve\AjaxRetrieve;
use WilokeListingTools\Controllers\Retrieve\NormalRetrieve;
use WilokeListingTools\Controllers\RetrieveController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\PlanHelper;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Routing\Controller;
use WP_User;

class AddListingController extends Controller
{
	public function __construct()
	{
		add_filter(
			'wilcity/filter/wiloke-listing-tools/configs/settings',
			[$this, 'addAddListingSettings'],
			10
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/AddListingController/getResults/callback/instafeedhub',
			[$this, 'setGetResultCallBackFuncCallback']
		);

		add_action('wp_ajax_wilcity_search_instafeedhub', [$this, 'searchForInstaFeedhub']);

		add_action('wiloke-listing-tools/addlisting', [$this, 'saveInstafeedHub'], 10, 3);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Validation/cleandata',
			[$this, 'setCleanValueFunc'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/config/listing-plan/listing_plan_settings',
			[$this, 'addToggleInstafeedHubToPlanSettings']
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/AddListingController/section/instafeedhub',
			[$this, 'addUserInfoToInstaFeed']
		);

		add_action('wp_ajax_wilcity_verify_token', [$this, 'verifyAndMaybeRenewToken']);
	}

	private function renewToken()
	{
		$oRetrieve = new RetrieveController(new NormalRetrieve());
		$aTokens = Option::getSiteTokens();
		if (empty($aTokens['refreshToken'])) {
			return $oRetrieve->error(['msg' => esc_html__('The refresh token is emptied', 'wilcity-instafeed-hub')]);
		}

		$response = wp_remote_post('https://instafeedhub.com/wp-json/instafeedhub/v1/renew-token', [
			'timeout'     => 5,
			'redirection' => 5,
			'blocking'    => true,
			'body'        => [
				'refreshToken' => $aTokens['refreshToken'],
				'accessToken'  => $aTokens['accessToken']
			]
		]);

		if (empty($response) || is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			return $oRetrieve->error(['msg' => esc_html__('Server Error', 'wilcity-instafeed-hub')]);
		}

		$aResponse = json_decode(wp_remote_retrieve_body($response), true);

		if ($aResponse['status'] == 'error') {
			return $oRetrieve->error(['msg' => $aResponse['msg']]);
		}

		return $oRetrieve->success(['accessToken' => $aResponse['accessToken']]);
	}

	public function verifyAndMaybeRenewToken()
	{
		$oRetrieve = new RetrieveController(new AjaxRetrieve());
		$aStatus = $this->middleware(['verifyNonce']);
		if ($aStatus['status'] === 'error') {
			$oRetrieve->error($aStatus);
		}

		$aTokens = Option::getUserTokens();
		if (empty($aTokens['accessToken'])) {
			$oUser = new WP_User(get_current_user_id());
			if (empty($oUser) || is_wp_error($oUser)) {
				$oRetrieve->error(['msg' => esc_html__('Forbidden', 'wilcity-instafeed-hub')]);
			}

			$response = wp_remote_post(WILOKE_INSTAFEEDHUB_LOGIN_REST . 'wilcity-customer/signin', [
				'timeout'     => 5,
				'redirection' => 5,
				'blocking'    => true,
				'body'        => [
					'email'          => $oUser->user_email,
					'nickname'       => $oUser->nickname,
					'whitelistedUrl' => home_url('/')
				]
			]);

			$aResponse = json_decode(wp_remote_retrieve_body($response), true);

			if (wp_remote_retrieve_response_code($response) != 200) {
				if (empty($aResponse)) {
					$msg = esc_html__('502 Bad gateway. Please wait for a few seconds and re-click the button again',
						'wilcity-instafeed-hub');
				} else {
					$msg = $aResponse['error']['msg'];
				}

				if (isset($aResponse["error"]["isTokenExpired"]) && $aResponse["error"]["isTokenExpired"]) {
					Option::deleteTokens(get_current_user_id());
				}

				return $oRetrieve->error(['msg' => $msg]);
			}

			if ($aResponse['status'] == 'error') {
				return $oRetrieve->error(['msg' => $aResponse['error']['msg']]);
			}

			Option::updateUserTokens(
				[
					'accessToken'  => $aResponse['accessToken'],
					'refreshToken' => $aResponse['refreshToken']
				]
			);

			return $oRetrieve->success(['accessToken' => $aResponse['accessToken']]);
		}

		$response = wp_remote_post(WILOKE_INSTAFEEDHUB_LOGIN_REST . 'verify-token', [
			'timeout'     => 5,
			'redirection' => 5,
			'blocking'    => true,
			'body'        => [
				'accessToken'    => Option::getUserTokens()['accessToken'],
				'whitelistedUrl' => home_url('/')
			]
		]);

		$aResponse = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($response) || is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			if (!isset($aResponse["error"]["isTokenExpired"]) || $aResponse["error"]["isTokenExpired"] === false) {
				return $oRetrieve->error(['msg' => esc_html__('Server Error', 'wilcity-instafeed-hub')]);
			}

			Option::deleteTokens(get_current_user_id());
		}

		if ($aResponse['status'] == 'error') {
			$aResponse = $this->renewToken();
			if ($aResponse['status'] == 'error') {
				return $oRetrieve->error(['msg' => $aResponse['msg']]);
			} else {
				$aTokens = Option::getSiteTokens();
				$aTokens['accessToken'] = $aResponse['accessToken'];
				Option::saveSiteTokens($aTokens);
			}
		} else {
			$aTokens = Option::getUserTokens();
		}

		return $oRetrieve->success(['accessToken' => $aTokens['accessToken']]);
	}

	public function addUserInfoToInstaFeed($aSection)
	{
		if (isset($_GET['postID'])) {
			$oUser = new WP_User(get_post_field('post_author', $_GET['postID']));
			$selectedInstafeedHub = GetSettings::getPostMeta($_GET['postID'], 'instafeedhub');
			$aSelectedInstafeedHub = json_decode($selectedInstafeedHub, true);

			if (isset($aSelectedInstafeedHub['id'])) {
				$aSection['fieldGroups']['instafeedhub']['redirectArgs'] = [
					'id' => $aSelectedInstafeedHub['id']
				];
			}
		} else {
			$oUser = new WP_User(get_current_user_id());
		}

		$aSection['fieldGroups']['instafeedhub']['ref'] = home_url('/');

		$aSection['fieldGroups']['instafeedhub']['instafeedHubUrl'] = WILOKE_INSTAFEEDHUB_DASHBOARD_URL;
		$aSection['fieldGroups']['instafeedhub']['loginRestAPI'] = WILOKE_INSTAFEEDHUB_LOGIN_REST;
		$aSection['fieldGroups']['instafeedhub']['instaIcon'] = WILCITY_INSTAFEED_HUB_URL . 'assets/img/icon.png';
		$aSection['fieldGroups']['instafeedhub']['hasAccessToken'] = !empty(Option::getSiteTokens()['accessToken']) ?
			'yes' : 'no';

		return $aSection;
	}

	public function addToggleInstafeedHubToPlanSettings($aPlanSettings)
	{
		array_push($aPlanSettings, [
			'type'      => 'wiloke_field',
			'fieldType' => 'select',
			'id'        => 'add_listing_plan:toggle_instafeedhub',
			'name'      => 'Toggle Instafeedhub',
			'options'   => [
				'enable'  => 'Enable',
				'disable' => 'Disable'
			]
		]);

		return $aPlanSettings;
	}

	public function saveInstafeedHub($that, $listingId, $planId)
	{
		$aData = $that->getData();
		if (!isset($aData['instafeedhub']) || !isset($aData['instafeedhub']['instafeedhub']) ||
			empty($aData['instafeedhub']['instafeedhub']) || !isset($aData['instafeedhub']['instafeedhub']['id']) ||
			empty($aData['instafeedhub']['instafeedhub']['id'])) {
			SetSettings::deletePostMeta($listingId, 'instafeedhub');
		} else {
			$aInstagram = $aData['instafeedhub']['instafeedhub'];
			$aInstagram['name'] = $aInstagram['label'];
			unset($aInstagram['label']);

			if (PlanHelper::isEnable($planId, 'instafeedhub')) {
				SetSettings::setPostMeta($listingId, 'instafeedhub', json_encode($aInstagram));
			}
		}
	}

	public function setGetResultCallBackFuncCallback($aSection)
	{
		return [App::get('WilcityInstafeedHubAddListingController'), 'getMyInstafeedHubValue'];
	}

	public function getMyInstafeedHubValue($aSection, $listingID)
	{
		$item = GetSettings::getPostMeta($listingID, 'instafeedhub');
		$aItem = json_decode($item, true);

		return [
			'instafeedhub' => empty($aItem) ? [] : [
				'id'    => $aItem['id'],
				'label' => $aItem['name']
			]
		];
	}

	public function searchForInstaFeedhub()
	{
		$oRetrieve = new RetrieveController(new AjaxRetrieve());
		if (isset($_GET['search']) && !empty($_GET['search'])) {
			$aResponse = InstafeedHub::search($_GET['search']);
			if ($aResponse['status'] == 'error') {
				$oRetrieve->error(['msg' => $aResponse['msg']]);
			} else {
				$aItems = [];
				foreach ($aResponse['items'] as $aItem) {
					$aItems[] = [
						'id'    => $aItem['id'],
						'label' => $aItem['title']
					];
				}
				$oRetrieve->success(['results' => $aItems]);
			}
		} else {
			$oRetrieve->error(['msg' => esc_html__('Please enter your keyword', 'wilcity-instafeedhub')]);
		}
	}

	public function setCleanValueFunc($cb, $sectionKey)
	{
		if ($sectionKey === 'instafeedhub') {
			return [
				App::get('WilcityInstafeedHubAddListingController'),
				'cleanInstagramData'
			];
		}

		return $cb;
	}

	public function cleanInstagramData($aResult, $sectionKey)
	{
		if (isset($aResult['instafeedhub'])) {
			$aResult['instafeedhub']['id'] = abs($aResult['instafeedhub']['id']);
			$aResult['instafeedhub']['label'] = substr(sanitize_text_field($aResult['instafeedhub']['label']), 0, 100);
		}

		return $aResult;
	}

	public function addAddListingSettings($aFields)
	{
		return array_merge($aFields, App::get('configs/addlisting'));
	}
}
