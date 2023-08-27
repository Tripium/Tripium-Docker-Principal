<?php
if (class_exists('\WooCommerce')) {
    $label = esc_html__('My Advanced Products', 'wilcity');
} else {
    $label = esc_html__('My products (Advanced) - (WooCommerce Plugin is required)', 'wilcity');
}

return [
  'myAdvancedProducts' => [
    'id'           => 'my_advanced_product_mode',
    'title'        => 'My Advanced Products',
    'object_types' => \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, true),
    'context'      => 'normal',
    'priority'     => 'low',
    'save_fields'  => false,
    'show_names'   => true, // Show field names on the left
    'fields'       => [
      [
        'type'       => 'select',
        'id'         => 'wilcity_my_advanced_product_mode',
        'name'       => 'Mode',
        'default_cb' => ['WilcityAdvancedProducts\MetaBoxes\AdvancedProduct', 'getMyProductMode'],
        'options'    => [
          'specify_products'     => 'Specify products',
          'specify_product_cats' => 'Specify Product Categories',
          'author_products'      => 'Get all author products',
          'inherit'              => 'Inherit Theme Options'
        ]
      ],
      [
        'name'       => 'Product Categories',
        'id'         => 'wilcity_my_advanced_product_cats',
        'type'       => 'term_ajax_search',
        'multiple'   => true,
        'limit'      => 10,
        'query_args' => [
          'taxonomy' => 'product_cat'
        ],
        'default_cb' => ['WilcityAdvancedProducts\MetaBoxes\AdvancedProduct', 'getMyProductCats']
      ],
      [
        'type'        => 'select2_posts',
        'description' => 'Showing WooCommerce Products on this Listing page',
        'post_types'  => ['product'],
        'attributes'  => [
          'ajax_action'   => 'wilcity_fetch_dokan_products',
          'post_types'    => 'product',
          'product_types' => 'simple'
        ],
        'id'          => 'wilcity_my_advanced_products',
        'multiple'    => true,
        'name'        => 'My Products',
        'default_cb'  => ['WilcityAdvancedProducts\MetaBoxes\AdvancedProduct', 'getMyProducts']
      ]
        //      [
        //        'name'       => 'Product Categories',
        //        'id'         => 'wilcity_my_advanced_product_cats1',
        //        'type'       => 'term_ajax_search',
        //        'multiple'   => true,
        //        'limit'      => 10,
        //        'query_args' => [
        //          'taxonomy' => 'product_cat'
        //        ],
        ////        'default_cb' => ['WilcityAdvancedProducts\MetaBoxes\AdvancedProduct', 'getMyProductCats']
        //      ]
    ]
  ],
];
