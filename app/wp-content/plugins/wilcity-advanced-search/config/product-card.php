<?php
return [
	'aButtonInfoOptions' => [
		[
			'name'  => 'Call Us',
			'value' => 'call_us',
			'key'   => 'call_us'
		],
		[
			'name'  => 'Email Us',
			'value' => 'email_us',
			'key'   => 'email_us'
		],
		[
			'name'  => 'Total Views',
			'value' => 'total_views',
			'key'   => 'total_views'
		]
	],
	'bodyFields'         => [
		'google_address' => [
			'name'    => 'Google Address',
			'hasIcon' => 'yes',
			'icon'    => 'la la-map-marker',
			'key'     => 'google_address',
			'type'    => 'google_address'
		],
		'phone'          => [
			'name'    => 'Phone',
			'hasIcon' => 'yes',
			'icon'    => 'la la-mobile',
			'key'     => 'phone',
			'type'    => 'phone'
		],
		'email'          => [
			'name'    => 'Email',
			'hasIcon' => 'yes',
			'icon'    => 'la la-envelope',
			'key'     => 'email',
			'type'    => 'email'
		],
		'website'        => [
			'name'    => 'Shop Url',
			'hasIcon' => 'yes',
			'icon'    => 'la la-link',
			'key'     => 'shop_url',
			'type'    => 'website'
		],
		'total_sales'    => [
			'name'    => 'Total Sales',
			'hasIcon' => 'yes',
			'icon'    => 'la la-money',
			'key'     => 'total_sales',
			'type'    => 'total_sales'
		],
		'single_price'   => [
			'name'    => 'Single Range',
			'hasIcon' => 'yes',
			'icon'    => 'la la-money',
			'key'     => 'single_price',
			'type'    => 'single_price'
		],
		'product_cats'   => [
			'name'    => 'Product Categories',
			'hasIcon' => 'yes',
			'icon'    => 'la la-certificate',
			'key'     => 'product_cats',
			'type'    => 'product_cats'
		],
		'product_tags'   => [
			'name'    => 'Product Tags',
			'hasIcon' => 'yes',
			'icon'    => 'la la-tag',
			'key'     => 'product_tags',
			'type'    => 'product_tags'
		],
//		'product_attributes' => [
//			'name'    => 'Product Attributes',
//			'hasIcon' => 'yes',
//			'icon'    => 'la la-tag',
//			'key'     => 'product_attributes',
//			'type'    => 'product_attributes'
//		]
	],
	'aFooter'            => [
		'taxonomy' => 'listing_cat'
	]
];