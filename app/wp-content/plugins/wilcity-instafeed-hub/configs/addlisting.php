<?php
return [
	'instafeedhub' => [
		'isDefault'           => true,
		'excludeGetBySection' => true,
		'type'                => 'instafeedhub',
		'key'                 => 'instafeedhub',
		'icon'                => 'la la-shopping-cart',
		'heading'             => 'Instagram Feedhub',
		'fieldGroups'         => [
			[
				'heading'           => 'Instagram Settings',
				'key'               => 'instafeedhub',
				'type'              => 'wil-instafeed-hub',
				'valueFormat'       => 'object',
				'isAjax'            => true,
				'selectValueFormat' => 'object',
				'loadOptionMode'    => 'ajax',
				'queryArgs'         => [
					'action' => 'wilcity_search_instafeedhub'
				],
				'iframeSrc'         => 'https://instafeedhub.com/insta-dashboard/',
				'fields'            => [
					[
						'label' => 'Label',
						'type'  => 'input',
						'desc'  => '',
						'key'   => 'label',
						'value' => 'Search for your Instagram'
					]
				]
			]
		]
	]
];
