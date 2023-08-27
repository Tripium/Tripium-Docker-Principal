<?php
return apply_filters('wilcity/filter/wilcity-advanced-woocommerce/configs/themeoptions', [
  'title'            => 'Advanced WooCommerce Settings',
  'id'               => 'woocommerce_advanced_settings',
  'icon'             => 'dashicons dashicons-cart',
  'subsection'       => false,
  'customizer_width' => '500px',
  'fields'           => [
    [
      'id'      => 'advanced_woo_get_product_mode',
      'type'    => 'select',
      'title'   => 'Product Mode',
      'default' => 'author_products',
      'options' => [
        'author_products'      => 'Author\'s Products',
        'specify_product_cats' => 'Specify Product Categories',
        'specify_products'     => 'Specify Products'
      ]
    ],
    [
      'id'          => 'advanced_woo_checkout_type_section_open',
      'type'        => 'section',
      'title'       => 'Checkout Settings',
      'description' => '',
      'indent'      => true
    ],
    [
      'id'      => 'advanced_woo_checkout_type',
      'type'    => 'select',
      'title'   => 'Checkout Type',
      'default' => 'redirect',
      'options' => [
        'redirect'          => 'Default WooCommerce Checkout',
        'directly_checkout' => 'Directly Checkout'
      ]
    ],
    [
      'id'          => 'advanced_woo_checkout_fields',
      'type'        => 'sorter',
      'title'       => 'Default Search Search By',
      'required'    => ['advanced_woo_checkout_type', '=', 'directly_checkout'],
      'description' => 'The fields are listed below will be used on Checkout Popup',
      'options'     => [
        'enabled'  => apply_filters(
          'wilcity/filter/wilcity-advanced-woocommerce/configs/woo-checkout-fields',
          [
            'first_name'    => 'First name',
            'last_name'     => 'Last name',
            'phone'         => 'Phone Number',
            'email'         => 'Email address',
            'address_1'     => 'Address',
            'customer_note' => 'Customer Note',
            'agreeToTerm'   => 'Agree To Term'
          ]
        ),
        'disabled' => []
      ]
    ],
    [
      'id'          => 'advanced_redirect_checkout_target',
      'type'        => 'select',
      'title'       => 'Checkout Action',
      'required'    => ['advanced_woo_checkout_type', '=', 'redirect'],
      'description' => 'When clicking on Checkout button, this setting will decide to open Checkout page in a new window or self page',
      'default'     => '_blank',
      'options'     => [
        '_blank' => 'New window',
        '_self'  => 'Self page'
      ]
    ],
    [
      'id'          => 'advanced_woo_checkout_type_section_close',
      'type'        => 'section',
      'description' => '',
      'indent'      => false
    ],
    [
      'id'          => 'wilcity_open_standard_product_settings',
      'type'        => 'section',
      'title'       => 'Standard WooCommerce Product Settings',
      'description' => '',
      'indent'      => true
    ],
    [
      'id'          => 'wilcity_toggle_send_sms_to_standard_products',
      'type'        => 'select',
      'title'       => 'Toggle Standard Product',
      'description' => 'If you want to send message when receiving an order on a Standard Product, please enable this feature',
      'options'     => [
        'enable'  => 'Enable',
        'disable' => 'Disable'
      ],
      'default'     => 'enable'
    ],
    [
      'id'          => 'wilcity_standard_received_purchased_product_message',
      'type'        => 'textarea',
      'title'       => 'Purchased Product Message (To Shop Owner)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'You get an order #%orderID% from %customerName%. Customer Phone Number: %customerPhoneNumber%'
    ],
    [
      'id'          => 'wilcity_standard_received_order_product_message',
      'type'        => 'textarea',
      'title'       => 'Placed an Order of Standard Product Message (To Shop Owner)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'You received an order from %customerName%. Order ID: %orderID%, Products: %productName%, Customer Phone: %customerPhoneNumber%'
    ],
    [
      'id'          => 'wilcity_standard_purchased_product_message',
      'type'        => 'textarea',
      'title'       => 'Purchased Product Message (To customer)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'Your order %orderID% has been processed successfully. Products: %productName%. Hotline: %shopOwnerPhoneNumber%'
    ],
    [
      'id'          => 'wilcity_standard_placed_order_product_message',
      'type'        => 'textarea',
      'title'       => 'Placed an Order of Standard Product Message (To Customer)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'Thank for using our service! We will contact you shortly. Your order ID is %orderID%. Hotline: %shopOwnerPhoneNumber%'
    ],
    [
      'id'     => 'wilcity_close_standard_product_settings',
      'type'   => 'section',
      'indent' => false
    ],
    [
      'id'          => 'wilcity_open_booking_product_settings',
      'type'        => 'section',
      'title'       => 'Booking WooCommerce Product Settings',
      'description' => '',
      'indent'      => true
    ],
    [
      'id'          => 'wilcity_toggle_send_sms_to_booking_products',
      'type'        => 'select',
      'title'       => 'Toggle Booking Product',
      'description' => 'If you want to send message when receiving an order on a Booking Product, please enable this feature',
      'options'     => [
        'enable'  => 'Enable',
        'disable' => 'Disable'
      ],
      'default'     => 'enable'
    ],
    [
      'id'          => 'wilcity_booking_received_purchased_product_message',
      'type'        => 'textarea',
      'title'       => 'Purchased Product Message (To Shop Owner)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'You received a booking order #%orderID% from %customerName%. %customerPhoneNumber%'
    ],
    [
      'id'          => 'wilcity_booking_received_placed_order_product_message',
      'type'        => 'textarea',
      'title'       => 'Purchased Product Message (To Shop Owner)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => '%customerName% placed an order on %productName%. Order ID: #%orderID%, Customer Phone Number: %customerPhoneNumber%'
    ],
    [
      'id'          => 'wilcity_booking_purchased_product_message',
      'type'        => 'textarea',
      'title'       => 'Purchased Booking Product Message (To Customer)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'Your order %orderID% has been processed successfully. %bookingInfo%. Hotline: %shopOwnerPhoneNumber%'
    ],
    [
      'id'          => 'wilcity_booking_placed_order_product_message',
      'type'        => 'textarea',
      'title'       => 'Placed An Order of Booking Product Message (To Customer)',
      'description' => 'Maximum 1600 character limit.',
      'default'     => 'Thank for using our service, We will contact you shortly! %bookingInfo%. Hotline: %shopOwnerPhoneNumber%'
    ],
    [
      'id'     => 'wilcity_close_booking_product_settings',
      'type'   => 'section',
      'indent' => false
    ]
  ]
]);
