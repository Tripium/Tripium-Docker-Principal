<?php
return [
	'default'  => [
		'instafeedhub' => [
			'name'           => 'Instafeed Hub',
			'key'            => 'instafeedhub',
			'baseKey'        => 'instafeedhub',
			'isDraggable'    => 'yes',
			'isWebview'      => 'yes',
			'icon'           => 'la la-cutlery',
			'isShowOnHome'   => 'no',
			'status'         => 'no',
			'excludeFromNav' => true,
			'vueKey'         => uniqid('instafeedhub')
		]
	],
	'settings' => [
		'instafeedhub' => [
			'fields' => ['common']
		]
	]
];
