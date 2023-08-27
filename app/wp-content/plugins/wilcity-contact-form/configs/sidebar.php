<?php

return [
  'sections' => [
    'contactForm7' => [
      'name'      => 'Contact Form 7',
      'key'       => 'contactForm7',
      'icon'      => 'la la-envelope-o',
      'status'    => 'yes',
      'baseKey'   => 'contactForm7',
      'isWebView' => true
    ]
  ],
  'fields'   => [
    'sections' => [
      'contactForm7' => [
        'fields' => [
          'common',
          [
            [
              'type'  => 'wil-textarea',
              'label' => 'Content',
              'key'   => 'content'
            ],
          ]
        ]
      ]
    ]
  ]
];
