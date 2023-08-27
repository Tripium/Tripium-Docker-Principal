<?php
return [
  [
    'id'     => 'wga_section',
    'title'  => 'Two-Factor Authentication Login',
    'type'   => 'section',
    'indent' => true
  ],
  [
    'id'      => 'wga_toggle',
    'type'    => 'select',
    'title'   => esc_html__('Toggle Two-Factor Authentication Login', 'wiloke-google-authenticator'),
    'options' => [
      'enable'  => 'Enable',
      'disable' => 'Disable'
    ],
    'default' => 'disable'
  ],
  [
    'id'      => 'wga_name',
    'type'    => 'text',
    'title'   => esc_html__('Two-Factor name', 'wiloke-google-authenticator'),
    'default' => get_option('blogname')
  ],
  [
    'id'          => 'wga_verify_page',
    'type'        => 'select',
    'title'       => esc_html__('Verification page', 'wiloke-google-authenticator'),
    'description' => esc_html__('You can skip this step if you are using custom login option',
      'wiloke-google-authenticator'),
    'data'        => 'posts',
    'args'        => [
      'post_type'      => 'page',
      'posts_per_page' => 100
    ]
  ],
  [
    'id'     => 'wga_section_close',
    'type'   => 'section',
    'indent' => false
  ]
];
