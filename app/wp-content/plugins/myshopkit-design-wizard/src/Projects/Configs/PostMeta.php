<?php

use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;

return [
	'image_configuration' => [
		'id'           => 'image_configuration',
		'title'        => esc_html__('Image Settings', 'myshopkit-design-wizard'),
		'object_types' => [AutoPrefix::namePrefix('my_projects')],
		'fields'       => [
			[
				'name'       => esc_html__('Content', 'myshopkit-design-wizard'),
				'id'         => 'project_content',
				'type'       => 'textarea',
				'save_field' => false,
				'default'    => ''
			],
			[
				'name'       => esc_html__('metadata', 'myshopkit-design-wizard'),
				'id'         => 'project_metadata',
				'type'       => 'textarea',
				'save_field' => false,
				'default'    => ''
			],
			[
				'name'       => esc_html__('Taxonomies', 'myshopkit-design-wizard'),
				'id'         => 'project_taxonomies',
				'type'       => 'textarea',
				'save_field' => false,
				'default'    => ''
			],
			[
				'name'       => esc_html__('Thumbnail ID', 'myshopkit-design-wizard'),
				'id'         => 'project_thumbnail_id',
				'type'       => 'text',
				'save_field' => false,
				'default'    => ''
			],
		]
	]
];