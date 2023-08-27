<?php

namespace WilokeOTPLogin\Helpers;

/**
 * Class User
 * @package WilokeOTPLogin\Helpers
 */
class User
{
	/**
	 * @var string
	 */
	private static $loginKey = 'wiloke_otp_login_code';

	/**
	 * @param $userId
	 * @param $otpCode
	 */
	public static function updateOTPCode($userId, $otpCode)
	{
		$prevOtpCode = self::getOTPCode($userId);
		if (!empty($prevOtpCode)) {
			wp_clear_scheduled_hook('wiloke_otp_delete_user_otp_code', [$userId, $prevOtpCode]);
		}

		update_user_meta($userId, self::$loginKey, $otpCode);
		wp_schedule_single_event(
			strtotime('+'.Option::getExpirationTime() . ' minutes'),
			'wiloke_otp_delete_user_otp_code',
			[$userId, $otpCode]
		);
	}

	/**
	 * @param $oUser
	 * @return mixed|string
	 */
	public static function getOTPCode($userId)
	{
		return get_user_meta($userId, self::$loginKey, true);
	}

	public static function deleteOTPCode($userId)
	{
		return delete_user_meta($userId, self::$loginKey);
	}

	public static function getUserIdByOTPCode($optCode)
	{
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s",
				self::$loginKey, $optCode)
		);
	}
}
