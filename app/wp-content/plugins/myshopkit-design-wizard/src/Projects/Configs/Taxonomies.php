<?php

return [
	'post_tag' => [
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
		'labels'                => [
			'name'              => esc_html__('Tags',  'myshopkit-design-wizard'),
			'singular_name'     => esc_html__('Tags', 'myshopkit-design-wizard'),
			'search_items'      => esc_html__('Search Tags', 'myshopkit-design-wizard')
		]
	],
];
