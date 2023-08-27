<?php


namespace WilokeInstagramFeedhub\Helpers;


use WilokeInstagramFeedhub\Controllers\RemoteDataController;
use WilokeListingTools\Controllers\Retrieve\NormalRetrieve;
use WilokeListingTools\Controllers\RetrieveController;
use WilokeListingTools\Framework\Helpers\GetSettings;

class InstafeedHub
{

	private static $aCacheInstaSettings = [];
	public static  $instaId;

	public static function search($s)
	{
		$oRetrieve = new RetrieveController(new NormalRetrieve());
		$aTokens = Option::getUserTokens();

		if (empty($aTokens['accessToken'])) {
			return $oRetrieve->error(['msg' => esc_html__('The Token is required', 'wilcity-instafeed-hub')]);
		}

		$response = wp_remote_get(WILOKE_INSTAFEEDHUB_REST . 'me/items?s=' . $s, [
			'headers' => [
				'Authorization' => 'Bearer ' . $aTokens['accessToken']
			]
		]);

		if (wp_remote_retrieve_response_code($response) != 200) {
			return $oRetrieve->error(['msg' => esc_html__('Oops! There is an issue with Instafeed server',
				'wilcity-instafeedhub')]);
		} else {
			$aResponse = json_decode(wp_remote_retrieve_body($response), true);

			if ($aResponse['status'] === 'error') {
				return $oRetrieve->error(['msg' => $aResponse['msg']]);
			}

			if (empty($aResponse['items'])) {
				return $oRetrieve->error(['msg' => esc_html__('Sorry, We did not found your item',
					'wilcity-instafeedhub')]);
			} else {
				return $oRetrieve->success($aResponse);
			}
		}
	}

	public static function fetchInstaItems($instaId, $authorId, $aArgs = [])
	{
		$oRetrieve = new RetrieveController(new NormalRetrieve());

		$aTokens = GetSettings::getUserMeta($authorId, 'instafeed_hub_token');
		if (empty($aTokens)) {
			return $oRetrieve->error(['msg' => esc_html__('Please create an account on instafeedhub.com first',
				'wilcity-instafeed-hub')]);
		}

		$response = wp_remote_get(WILOKE_INSTAFEEDHUB_REST . 'me/insta-user/' . $instaId . '/media', [
			'headers' => [
				'Authorization' => 'Bearer ' . $aTokens['accessToken']
			],
			'body'    => $aArgs
		]);

		if (wp_remote_retrieve_response_code($response) != 200) {
			return $oRetrieve->error(['msg' => esc_html__('We found no Instagram Items',
				'wilcity-instafeedhub')]);
		} else {
			$aResponse = json_decode(wp_remote_retrieve_body($response), true);

			if ($aResponse['status'] === 'error') {
				return $oRetrieve->error(['msg' => $aResponse['msg']]);
			}

			if (empty($aResponse['posts'])) {
				return $oRetrieve->error(['msg' => esc_html__('Sorry, We did not found any instagram Images',
					'wilcity-instafeedhub')]);
			} else {
				return $oRetrieve->success($aResponse['posts']);
			}
		}
	}

	public static function getInstaId($thePost = '')
	{
		if (empty($thePost)) {
			if (!is_singular()) {
				return false;
			}

			global $post;
		} else {
			$post = $thePost;
		}

		if (!empty(self::$instaId)) {
			return self::$instaId;
		}
		$aSettings = json_decode(GetSettings::getPostMeta($post->ID, 'instafeedhub'), true);
		if (empty($aSettings) || !isset($aSettings['id']) || empty($aSettings['id'])) {
			return false;
		}

		self::$instaId = $aSettings['id'];

		return self::$instaId;
	}

	public static function getInstaSettings($instaId)
	{
		if (isset(self::$aCacheInstaSettings[$instaId])) {
			return self::$aCacheInstaSettings[$instaId];
		}

		$aOptions = Option::get(RemoteDataController::$optionKey);

		if (empty($aOptions) || !is_array($aOptions) || !isset($aOptions[$instaId])) {
			return false;
		}

		self::$aCacheInstaSettings[$instaId] = $aOptions[$instaId];
		return self::$aCacheInstaSettings[$instaId];
	}
}
