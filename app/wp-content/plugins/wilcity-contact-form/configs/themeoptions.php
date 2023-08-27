<?php
return [
  [
    'id'     => 'contact_form_section_open',
    'type'   => 'section',
    'title'  => 'Contact Form',
    'indent' => true
  ],
  [
    'id'      => 'contact_form_toggle',
    'type'    => 'select',
    'title'   => 'Is Hide Contact Form on Unclaimed Listing?',
    'default' => 'yes',
    'options' => [
      'enable'  => 'Yes',
      'disable' => 'No'
    ]
  ],
  [
    'id'     => 'contact_form_section_open_close',
    'type'   => 'section',
    'indent' => false
  ]
];
