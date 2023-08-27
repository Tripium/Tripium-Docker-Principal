<?php

use WooKit\Shared\AutoPrefix;

$aLabels = [
	'name'                  => esc_html__( 'Smart Bars', 'wookit' ),
	'singular_name'         => esc_html__( 'Smart Bar', 'wookit' ),
	'menu_name'             => esc_html__( 'Smart Bars', 'wookit' ),
	'name_admin_bar'        => esc_html__( 'Smart Bars', 'wookit' ),
	'archives'              => esc_html__( 'Item Archives', 'wookit' ),
	'attributes'            => esc_html__( 'Item Attributes', 'wookit' ),
	'parent_item_colon'     => esc_html__( 'Parent Item:', 'wookit' ),
	'all_items'             => esc_html__( 'All Items', 'wookit' ),
	'add_new_item'          => esc_html__( 'Add New Item', 'wookit' ),
	'add_new'               => esc_html__( 'Add New', 'wookit' ),
	'new_item'              => esc_html__( 'New Item', 'wookit' ),
	'edit_item'             => esc_html__( 'Edit Item', 'wookit' ),
	'update_item'           => esc_html__( 'Update Item', 'myshopkit' ),
	'view_item'             => esc_html__( 'View Item', 'myshopkit' ),
	'view_items'            => esc_html__( 'View Items', 'myshopkit' ),
	'search_items'          => esc_html__( 'Search Item', 'myshopkit' ),
	'not_found'             => esc_html__( 'Not found', 'myshopkit' ),
	'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'myshopkit' ),
	'featured_image'        => esc_html__( 'Featured Image', 'myshopkit' ),
	'set_featured_image'    => esc_html__( 'Set featured image', 'myshopkit' ),
	'remove_featured_image' => esc_html__( 'Remove featured image', 'myshopkit' ),
	'use_featured_image'    => esc_html__( 'Use as featured image', 'myshopkit' ),
	'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'myshopkit' ),
	'items_list'            => esc_html__( 'Items list', 'myshopkit' ),
	'items_list_navigation' => esc_html__( 'Items list navigation', 'myshopkit' ),
	'filter_items_list'     => esc_html__( 'Filter items list', 'myshopkit' ),
];

return [
	'label'              => esc_html__( 'Smart Bars', 'myshopkit' ),
	'labels'             => $aLabels,
	'public'             => true,
	'publicly_queryable' => true,
	'show_ui'            => true,
	'show_in_menu'       => true,
	'query_var'          => true,
	'rewrite'            => [ 'slug' => AutoPrefix::namePrefix( 'smartbar' ) ],
	'capability_type'    => 'post',
	'has_archive'        => true,
	'hierarchical'       => true,
	'menu_position'      => null,
	'supports'           => [ 'title', 'editor', 'thumbnail', 'author' ]
];
