<?php

use WooKit\Shared\AutoPrefix;

$labels = [
	'name'           => esc_html__( 'Slide In', 'wookit' ),
	'singular_name'  => esc_html__( 'Slide In', 'wookit' ),
	'menu_name'      => esc_html__( 'Slide In', 'wookit' ),
	'name_admin_bar' => esc_html__( 'Slide In', 'wookit' )
];

return [
	'description'        => esc_html__( 'The setting for your Slide in', 'wookit' ),
	'labels'             => $labels,
	'public'             => true,
	'publicly_queryable' => true,
	'show_ui'            => true,
	'show_in_menu'       => true,
	'query_var'          => true,
	'rewrite'            => [ 'slug' => AutoPrefix::namePrefix( 'slidein' ) ],
	'post_type'          => AutoPrefix::namePrefix( 'slidein' ),
	'capability_type'    => 'post',
	'has_archive'        => true,
	'hierarchical'       => true,
	'menu_position'      => null,
	'supports'           => [ 'title', 'editor', 'thumbnail', 'author' ]
];
