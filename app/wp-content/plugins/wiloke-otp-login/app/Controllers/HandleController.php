<?php


namespace WilokeOTPLogin\Controllers;


use WilokeOTPLogin\Helpers\Option;
use WilokeOTPLogin\Helpers\User;

class HandleController
{
	private $aOptions;
	private $otpCode;
	private $oUser;
	public  $errMessage;

	/**
	 * OTPLoginController constructor.
	 */
	public function __construct()
	{
		add_filter(
			'wiloke-otp-login/filter/Controllers/HandleController/maybeSendOTP',
			[$this, 'maybeSendOTP'],
			10,
			2
		);

		add_filter(
			'wiloke-otp-login/filter/Controllers/HandleController/verifyOTPCode',
			[$this, 'verifyOTPCode'],
			10,
			2
		);

		add_action(
			'wiloke_otp_delete_user_otp_code',
			[$this, 'deleteUserOTPCode'],
			10
		);
	}

	/**
	 * @param $userId
	 */
	public function deleteUserOTPCode($userId)
	{
		User::deleteOTPCode($userId);
	}

	/**
	 * @param $aStatus
	 * @param $emailOrUsername
	 * @return array
	 */
	public function maybeSendOTP($aStatus, $emailOrUsername)
	{
		if (!Option::isEnable()) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('OTP Login feature is disabled. Please go to Wiloke OTP Login and enable it.',
					'wiloke-otp-login')
			];
		}

		if (!email_exists($emailOrUsername) && !username_exists($emailOrUsername)) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('Invalid username or email', 'wiloke-otp-login')
			];
		}

		if (username_exists($emailOrUsername)) {
			$this->oUser = get_user_by('login', $emailOrUsername);
		} else {
			$this->oUser = get_user_by('email', $emailOrUsername);
		}

		$status = wp_mail($this->oUser->user_email, Option::getOTPField('email_subject'),
			$this->generateOTPCodeEmailContent());

		if (!$status) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('Sorry, We could not send the email. Please recheck PHP Mail function and make sure that it is enabling',
					'wiloke-otp-login')
			];
		}

		User::updateOTPCode($this->oUser->ID, $this->otpCode);

		return [
			'status' => 'success',
			'msg'    => sprintf(esc_html__('We sent OTP code to your email. The code is expired after %s minutes',
				'wiloke-otp-login'), Option::getExpirationTime())
		];
	}

	/**
	 * @param $aStatus
	 * @param $optCode
	 * @return array
	 */
	public function verifyOTPCode($aStatus, $optCode)
	{
		if (!Option::isEnable()) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('OTP Login feature is disabled. Please go to Wiloke OTP Login and enable it.',
					'wiloke-otp-login')
			];
		}

		$userId = User::getUserIdByOTPCode($optCode);

		if (empty($userId)) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('Invalid OTP Code', 'wiloke-otp-login')
			];
		}

		$this->deleteUserOTPCode($userId);

		return [
			'status' => 'success',
			'msg'    => esc_html__('The code has been verified successfully', 'wiloke-otp-login'),
			'userId' => $userId
		];
	}

	/**
	 * @return false|string
	 */
	public function generateOTPCode()
	{
		$generator = "1357902468";
		$this->otpCode = '';
		for ($i = 1; $i <= 6; $i++) {
			$this->otpCode .= substr($generator, (rand() % (strlen($generator))), 1);
		}

		return $this->otpCode;
	}

	/**
	 * @param $content
	 * @return mixed
	 */
	public function generateOTPCodeEmailContent()
	{
		$otpCode = $this->generateOTPCode();

		$content = str_replace(
			[
				'%OTPcode%',
				'%otpCode%',
				'%otpcode%',
			],
			[
				$otpCode,
				$otpCode,
				$otpCode,
			],
			Option::getOTPField('email_content')
		);

		return stripslashes($content);
	}

	public function secondLoginScreen()
	{
		include WILOKE_OTP_VIEWS . 'wiloke-otp-login-screen.php';
	}
}
