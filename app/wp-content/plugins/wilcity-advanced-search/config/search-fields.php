<?php
return [
	'new_price_range' => [
		'adminCategory' => 'wil-new-price-range',
		'type'          => 'wil-search-dropdown',
		'childType'     => 'wil-price-range',
		'valueFormat'   => 'object',
		'value'         => [
			'min' => 0,
			'max' => 0
		],
		'label'         => 'New Price range',
		'key'           => 'price_range',
		'originalKey'   => 'new_price_range',
		'isDefault'     => true
	],
	'orderby'         => [
		'adminCategory' => 'wil-orderby',
		'type'          => 'wil-search-dropdown',
		'oldType'       => 'wil-select-tree',
		'childType'     => 'wil-radio',
		'label'         => 'Order By',
		'key'           => 'orderby',
		'isDefault'     => true
	],
	'order'           => [
		'adminCategory' => 'wil-order',
		'type'          => 'wil-search-dropdown',
		'oldType'       => 'wil-select-tree',
		'childType'     => 'wil-radio',
		'label'         => 'Sort By',
		'key'           => 'order',
		'isDefault'     => true
	],
	'best_viewed'     => [
		'adminCategory' => 'wil-other',
		'type'          => 'wil-toggle-btn',  // checkbox
		'oldType'       => 'wil-switch',
		'label'         => 'Most Viewed',
		'key'           => 'best_viewed',
		'isDefault'     => true
	],
	'newest'          => [
		'adminCategory' => 'wil-other',
		'type'          => 'wil-toggle-btn',  // checkbox
		'oldType'       => 'wil-switch',
		'label'         => 'Newest',
		'key'           => 'newest',
		'isDefault'     => true
	],
	'recommended'     => [
		'adminCategory' => 'wil-other',
		'type'          => 'wil-toggle-btn', // dropdown
		'oldType'       => 'wil-switch',
		//            'type'          => 'checkbox',
		'label'         => 'Recommended',
		'key'           => 'recommended',
		'isDefault'     => true
	],
	'best_rated'      => [
		'adminCategory'  => 'wil-other',
		'type'           => 'wil-toggle-btn', // checkbox
		'oldType'        => 'wil-switch',
		'childType'      => 'checkbox',
		'label'          => 'Rating',
		'key'            => 'best_rated',
		'notInPostTypes' => ['event'],
		'isDefault'      => true
	],
	'discount'        => [
		'adminCategory' => 'wil-other',
		'type'          => 'wil-toggle-btn', // checkbox
		'oldType'       => 'wil-switch',
		'label'         => 'Discount',
		'key'           => 'discount',
		'isDefault'     => true
	],
	'best_sales'      => [
		'adminCategory' => 'wil-other',
		'type'          => 'wil-toggle-btn', // checkbox
		'oldType'       => 'wil-switch',
		'label'         => 'Top sales',
		'key'           => 'best_sales',
		'isDefault'     => true
	],
	'nearbyme'        => [
		'adminCategory' => 'wil-other',
		'type'          => 'wil-toggle-btn',
		'oldType'       => 'wil-switch',
		'label'         => 'Near By Me',
		'desc'          => 'To setup radius and unit, please go to Appearance -> Theme Options -> Directory Type',
		'key'           => 'nearbyme',
		'isDefault'     => true
	],
	'custom_taxonomy' => [
		'adminCategory'    => 'wil-term',
		'type'             => 'wil-search-dropdown', // select
		'childType'        => 'wil-checkbox', // select 2
		'oldType'          => 'wil-select-tree',
		'label'            => 'Custom Taxonomy',
		'group'            => 'term',
		'key'              => 'custom_taxonomy',
		'originalKey'      => 'custom_taxonomy',
		'ajaxAction'       => 'wilcity_select2_fetch_term',
		'adminQueryArgs'   => [
			'action'  => 'wilcity_fetch_woo_taxonomies',
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'mode'    => 'option'
		],
		'isAjax'           => 'no',
		'isShowParentOnly' => 'no',
		'orderBy'          => 'count',
		'isMultiple'       => 'yes',
		'order'            => 'DESC',
		'isHideEmpty'      => 'no',
		'isDefault'        => true,
		'isCustom'         => true,
		'isClone'          => true
	]
];
