<?php

namespace WilokeInstagramFeedhub\Helpers;

use WilokeInstagramFeedhub\Controllers\RemoteDataController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;

/**
 * Class Option
 * @package WilokeInstaFeedHub\Helpers
 */
class Option
{
	private static $aCacheInstaSettings = [];
	private static $tokenKey            = 'instafeedhub_tokens';

	/**
	 * @param $key
	 * @return mixed|void
	 */
	public static function get($key)
	{
		return get_option($key);
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public static function update($key, $value)
	{
		update_option($key, $value);
	}

	public static function getUserTokens()
	{
		$aTokens = GetSettings::getUserMeta(get_current_user_id(), 'instafeed_hub_token');

		if (empty($aTokens)) {
			if (current_user_can('administrator')) {
				return self::getSiteTokens();
			}

			return ['accessToken' => '', 'refreshToken' => ''];
		}

		return $aTokens;
	}

	public static function deleteTokens($userId) {
		SetSettings::deleteUserMeta($userId, 'instafeed_hub_token');
	}

	public static function updateUserTokens($aTokens)
	{
		SetSettings::setUserMeta(get_current_user_id(), 'instafeed_hub_token', $aTokens);

		return $aTokens;
	}

	/**
	 * @param $aTokens ['accessToken' => '', 'refreshToken' => '']
	 */
	public static function saveSiteTokens($aTokens)
	{
		update_option(self::$tokenKey, $aTokens);

		return $aTokens;
	}

	public static function getSiteTokens()
	{
		$aTokens = get_option(self::$tokenKey);
		if (empty($aTokens)) {
			return ['accessToken' => '', 'refreshToken' => ''];
		}

		return $aTokens;
	}

	public static function getInstaSettings($instaId)
	{
		if (isset(self::$aCacheInstaSettings[$instaId])) {
			return self::$aCacheInstaSettings[$instaId];
		}

		$aOptions = self::get(RemoteDataController::$optionKey);

		if (empty($aOptions) || !is_array($aOptions) || !isset($aOptions[$instaId])) {
			return false;
		}

		foreach ($aOptions[$instaId] as $key => $val) {
			$aOptions[$instaId][$key] = InstaSettingValueFormat::correctValueType($val, $key);
			$aOptions[$instaId]['placements'] = isset($val['placements']) && is_array($val['placements']) ?
				$val['placements'] : [];
		}
		self::$aCacheInstaSettings[$instaId] = $aOptions[$instaId];
		return self::$aCacheInstaSettings[$instaId];
	}
}
