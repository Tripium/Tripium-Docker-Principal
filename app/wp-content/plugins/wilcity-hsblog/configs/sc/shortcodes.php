<?php
return [
	'wilcity_kc_hsblog_cats' => [
		'name'     => 'HsBlog Categories',
		'icon'     => 'sl-paper-plane',
		'group'    => 'term',
		'css_box'  => true,
		'category' => WILCITY_SC_CATEGORY,
		'params'   => [
			'general' => [
				'heading',
				'heading_color',
				'desc',
				'desc_color',
				'header_desc_text_align',
				'hsblog_cats'
			],
			'design'  => 'bootstrap_columns',
			'styling' => [
				[
					'name' => 'css_custom',
					'type' => 'css'
				]
			]
		]
	]
];
