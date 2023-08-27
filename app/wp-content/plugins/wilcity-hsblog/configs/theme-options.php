<?php
return [
	'hsblog_settings' => [
		'title'            => 'HsBlog Settings',
		'id'               => 'hsblog_settings',
		'subsection'       => false,
		'icon'             => 'dashicons dashicons-admin-generic',
		'customizer_width' => '500px',
		'fields'           => [
//			[
//				'id'     => 'hsblog_section_open',
//				'title'  => 'HsBlog Settings',
//				'type'   => 'section',
//				'indent' => true
//			],
//			[
//				'id'      => 'toggle_hsblog',
//				'title'   => 'Using HsBlog Instead of Wilcity Blog',
//				'type'    => 'select',
//				'options' => [
//					'enable'  => 'Enable',
//					'disable' => 'Disable'
//				]
//			],
			[
				'id'          => 'hsblog_base_url',
				'title'       => 'Hsblog Base Url',
//				'required'    => ['toggle_hsblog', '=', 'enable'],
				'type'        => 'text',
				'description' => 'EG: http://highspeedblog.com/'
			],
//			[
//				'id'     => 'hsblog_section_open_close',
//				'title'  => '',
//				'type'   => 'section',
//				'indent' => false
//			]
		]
	]
];
