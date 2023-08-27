<?php
return [
  [
    'name'     => esc_html__('Google Authenticator Settings', 'wiloke-google-authenticator'),
    'desc'     => esc_html__('Google authenticator setting', 'wiloke-google-authenticator'),
    'id'       => 'ga_setting',
    'type'     => 'title',
    'on_front' => false,
  ],
  [
    'name'             => 'Google authenticator mode',
    'id'               => 'wiloke_ga_mode',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => 'disable',
    'options'          => [
      'disable' => esc_html__('Disable', 'wiloke-google-authenticator'),
      'enable'  => esc_html__('Enable', 'wiloke-google-authenticator')
    ],
  ],
  [
    'name'        => 'Secret code',
    'desc'        => '',
    'default'     => '',
    'id'          => 'wiloke_ga_secret_code',
    'type'        => 'text',
    'attributes'  => [
      'readonly' => 'readonly',
    ],
    'after_field' => ''
  ],
  [
    'name'        => 'Verify OTP to Enable this feature',
    'desc'        => '<button id="wga-verify-opt-code" class="button button-primary">Verify OTP Code</button><ol><li>Switch Google authenticator mode to <strong>Enable</strong> status</li><li>In the app, tap on Menu and select "Set up account"</li><li>Select "Scan a barcode"</li><li>
After you have scanned the QR code and created an account, enter the verification code from the scanned account here.</li></ol>',
    'default'     => '',
    'id'          => 'wiloke_ga_opt_verification',
    'type'        => 'text',
    'after_field' => '',
  ]
];
