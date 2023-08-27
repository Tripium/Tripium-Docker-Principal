<?php

use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;

return [
	'projects' => [
		'labels'             => [
			'name'          => esc_html__('My Projects', 'myshopkit-design-wizard'),
			'singular_name' => esc_html__('My Project', 'myshopkit-design-wizard'),
			'menu_name'     => esc_html__('My Projects', 'myshopkit-design-wizard'),
		],
		'description'        => esc_html__('Description.', 'myshopkit-design-wizard'),
		'public'             => true,
		'menu_icon'          => 'dashicons-admin-post',
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'show_in_rest'       => true,
		'postType'           => AutoPrefix::namePrefix('my_projects'),
		'rest_base'          => AutoPrefix::namePrefix('my_projects'),
		'rewrite'            => [
			'slug' => AutoPrefix::namePrefix('my_projects')
		],
		'map_meta_cap'       => true,
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => null,
		'supports'           => [
			'title',
			'author',
			'thumbnail'
		]
	]
];
