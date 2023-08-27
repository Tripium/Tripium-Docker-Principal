<?php
return apply_filters(
  'wilcity/wilcity-advanced-woocommerce/configs/checkout-popup',
  [
    'first_name'    => [
      'type'       => 'wil-input',
      'childType'  => 'text',
      'label'      => esc_html__('First name', 'wilcity-advanced-woocommerce'),
      'key'        => 'first_name',
      'isRequired' => true
    ],
    'last_name'     => [
      'type'       => 'wil-input',
      'childType'  => 'text',
      'label'      => esc_html__('Last name', 'wilcity-advanced-woocommerce'),
      'key'        => 'last_name',
      'isRequired' => true
    ],
    'phone'         => [
      'type'       => 'wil-input',
      'childType'  => 'text',
      'label'      => esc_html__('Phone Number', 'wilcity-advanced-woocommerce'),
      'key'        => 'phone',
      'isRequired' => true
    ],
    'email'         => [
      'type'       => 'wil-input',
      'childType'  => 'email',
      'label'      => esc_html__('Email', 'wilcity-advanced-woocommerce'),
      'key'        => 'email',
      'validateCb' => 'is_email',
      'isRequired' => true
    ],
    'address_1'     => [
      'type'       => 'wil-textarea',
      'childType'  => 'text',
      'label'      => esc_html__('Billing Address', 'wilcity-advanced-woocommerce'),
      'key'        => 'address_1',
      'isRequired' => true
    ],
    'customer_note' => [
      'type'      => 'wil-textarea',
      'childType' => 'text',
      'label'     => esc_html__('Customer Note', 'wilcity-advanced-woocommerce'),
      'key'       => 'customer_note'
    ],
    'agreeToTerm'   => [
      'type'       => 'wil-checkbox',
      'label'      => __('By clicking on Book Now, I agree to your term and policy',
        'wilcity-advanced-woocommerce'),
      'msg'        => esc_html__('In order to process checkout, You have to agree to our term and policy',
        'wilcity-advanced-woocommerce'),
      'key'        => 'agreeToTerm',
      'isRequired' => true
    ]
  ]
);
