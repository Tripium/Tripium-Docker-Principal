<?php

use WilokeListingTools\Framework\Helpers\General;

return [
  'fields'     => [
    'opentable' => [
      'type'                => 'opentable',
      'key'                 => 'opentable',
      'excludeGetBySection' => true, // @see PrintAddListingSettings::getResults
      'icon'                => 'la la-file-text',
      'heading'             => 'Open Table',
      'fieldGroups'         => [
        [
          'heading'           => 'Open Table Name',
          'type'              => 'wil-select-tree',
          'loadOptionMode'    => 'ajax',
          'valueFormat'       => 'object',
          'selectValueFormat' => 'object',
          'desc'              => '',
          'isAjax'            => true,
          'maximum'           => 1,
          'queryArgs'         => [
            'mode'   => 'select',
            'action' => 'wilcity_fetch_my_opentable'
          ],
          'key'               => 'my_opentable',
          'fields'            => [
            [
              'label' => 'Label',
              'type'  => 'input',
              'key'   => 'label',
              'value' => 'Restaurant Name'
            ],
            [
              'label' => 'Is Required?',
              'type'  => 'checkbox',
              'desc'  => '',
              'key'   => 'isRequired',
              'value' => 'yes'
            ]
          ]
        ]
      ]
    ],
  ],
  'metabox'    => [
    'opentable' => [
      'id'           => 'wilcity_opentable',
      'save_fields'  => false,
      'title'        => 'My Open Table',
      'object_types' => General::getPostTypeKeys(false, true),
      'context'      => 'normal',
      'priority'     => 'low',
      'show_names'   => true, // Show field names on the left
      'fields'       => [
        [
          'type'        => 'select2_posts',
          'description' => 'Search for your table id on opentable.com',
          'attributes'  => [
            'ajax_action' => 'wilcity_fetch_my_opentable'
          ],
          'id'          => 'wilcity_my_opentable',
          'multiple'    => false
        ]
      ]
    ],
  ],
  'navigation' => [
    'default'  => [
      'opentable' => [
        'name'           => 'Opentable',
        'key'            => 'opentable',
        'baseKey'        => 'opentable',
        'isDraggable'    => 'yes',
        'isWebview'      => 'yes',
        'icon'           => 'la la-cutlery',
        'isShowOnHome'   => 'no',
        'status'         => 'no',
        'excludeFromNav' => true,
        'vueKey'         => uniqid('opentable')
      ]
    ],
    'settings' => [
      'opentable' => [
        'fields' => ['common']
      ]
    ]
  ],
  'sidebar'    => [
    'default'  => [
      'name'      => 'Opentable',
      'key'       => 'opentable',
      'baseKey'   => 'opentable',
      'isWebview' => 'yes',
      'icon'      => 'la la-cutlery',
      'status'    => 'no'
    ],
    'settings' => [
      'opentable' => [
        'fields' => ['common']
      ]
    ]
  ]
];
