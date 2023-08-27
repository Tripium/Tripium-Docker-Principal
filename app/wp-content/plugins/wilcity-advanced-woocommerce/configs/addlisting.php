<?php
return [
  'my_advanced_products' => [
    'isDefault'           => true,
    'excludeGetBySection' => true,
    'type'                => 'my_advanced_products',
    'key'                 => 'my_advanced_products',
    'icon'                => 'la la-shopping-cart',
    'heading'             => 'My Advanced Products',
    'fieldGroups'         => [
      [
        'heading'           => 'Mode',
        'key'               => 'my_advanced_product_mode',
        'type'              => 'wil-select-tree',
        'selectValueFormat' => 'id',
        'loadOptionMode'    => 'default',
        'fields'            => [
          [
            'label' => 'Label',
            'type'  => 'input',
            'desc'  => '',
            'key'   => 'label',
            'value' => 'Mode'
          ]
        ]
      ],
      [
        'heading'           => 'Product Category',
        'key'               => 'my_advanced_product_cats',
        'type'              => 'wil-select-tree',
        'dependency'        => [
          'parent'  => 'my_advanced_product_mode',
          'compare' => '=',
          'value'   => 'specify_product_cats'
        ],
        'isAjax'            => true,
        'selectValueFormat' => 'object',
        'loadOptionMode'    => 'ajax',
        'queryArgs'         => [
          'action' => 'wilcity_fetch_product_cats'
        ],
        'fields'            => [
          [
            'label' => 'Label',
            'type'  => 'input',
            'desc'  => '',
            'key'   => 'label',
            'value' => 'Pickup product categories'
          ],
          [
            'label' => 'Maximum Categories can be used',
            'type'  => 'input',
            'key'   => 'maximum',
            'value' => 4
          ]
        ]
      ],
      [
        'heading'           => 'Product Settings',
        'key'               => 'my_advanced_products',
        'dependency'        => [
          'parent'  => 'my_advanced_product_mode',
          'compare' => '=',
          'value'   => 'specify_products'
        ],
        'type'              => 'wil-select-tree',
        'isAjax'            => true,
        'selectValueFormat' => 'object',
        'loadOptionMode'    => 'ajax',
        'queryArgs'         => [
          'action' => 'wilcity_fetch_dokan_products',
        ],
        'fields'            => [
          [
            'label' => 'Label',
            'type'  => 'input',
            'desc'  => '',
            'key'   => 'label',
            'value' => 'Showing Products on the Listing'
          ],
          [
            'label' => 'Maximum Products can be used',
            'type'  => 'input',
            'key'   => 'maximum',
            'value' => 4
          ]
        ]
      ]
    ]
  ]
];
