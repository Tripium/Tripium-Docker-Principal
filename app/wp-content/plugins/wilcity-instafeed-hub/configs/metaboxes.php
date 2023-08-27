<?php
return [
	'instafeedhub' => [
		'id'           => 'instafeedhub',
		'title'        => 'Instafeed Hub',
		'object_types' => \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, true),
		'context'      => 'normal',
		'priority'     => 'low',
		'save_fields'  => false,
		'show_names'   => true, // Show field names on the left
		'fields'       => [
			[
				'name'       => 'Search for your Instagram on InstafeedHub',
				'id'         => 'wilcity_instafeedhub',
				'type'        => 'select2_posts',
				'attributes'  => [
					'ajax_action'   => 'wilcity_admin_search_instafeedhub'
				],
				'default_cb' => ['WilcityAdvancedProducts\MetaBoxes\ListingMetaBoxes', 'getInstafeedHub'],
				'options'    => [
					'name' => 'value'
				]
			]
		]
	],
];
