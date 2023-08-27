<?php
return [
	'sendOTPStep'   => [
		[
			'type'        => 'wil-input',
			'label'       => esc_html__('Enter your username or email', 'wiloke-otp-login'),
			'translation' => 'username',
			'name'        => 'user_login',
			'isRequired'  => 'yes'
		]
	],
	'verifyOtpStep' => [
		[
			'type'        => 'wil-input',
			'label'       => esc_html__('Enter the OTP code', 'wiloke-otp-login'),
			'translation' => 'enterInOTPCode',
			'name'        => 'otpCode',
			'isRequired'  => 'yes'
		]
	],
	'steps'         => [
		'resendOTP'   => 'verifyOtpStep',
		'sendOTPStep' => 'verifyOtpStep',
	]
];
