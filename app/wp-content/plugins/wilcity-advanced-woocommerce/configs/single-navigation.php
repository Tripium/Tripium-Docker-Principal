<?php
return [
  'sections' => [
    'advanced_simple_product_multiple_selection' => [
      'name'               => 'My Products (Advanced)',
      'key'                => 'advanced_simple_product_multiple_selection',
      'isDraggable'        => 'yes',
      'variant'            => 'multiple_selection',
      'icon'               => 'la la-shopping-cart',
      'isShowOnHome'       => 'no',
      'isShowBoxTitle'     => 'yes',
      'maximumItemsOnHome' => 3,
      'status'             => 'no',
      'baseKey'            => 'my_advanced_products'
    ],
    'advanced_simple_product_single_selection'   => [
      'name'               => 'My Products (Advanced)',
      'key'                => 'advanced_simple_product_single_selection',
      'isDraggable'        => 'yes',
      'variant'            => 'single_selection',
      'icon'               => 'la la-shopping-cart',
      'isShowOnHome'       => 'no',
      'isShowBoxTitle'     => 'yes',
      'maximumItemsOnHome' => 3,
      'status'             => 'no',
      'baseKey'            => 'my_advanced_products'
    ]
  ],
  'settings' => [
    'my_advanced_products' => [
      'fields' => ['common']
    ]
  ]
];
