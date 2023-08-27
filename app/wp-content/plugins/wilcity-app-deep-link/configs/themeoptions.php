<?php
return [
	[
		'id'     => 'mobile_app_deep_link_settings_open',
		'title'  => 'Mobile App Options Settings',
		'type'   => 'section',
		'indent' => true
	],
	[
		'id'      => 'mobile_app_toggle_deep_link',
		'type'    => 'select',
		'options' => [
			'enable'  => 'Enable',
			'disable' => 'Disable'
		],
		'title'   => 'Toggle Share Deep Link',
		'default' => 'disable'
	],
	[
		'id'    => 'mobile_app_deep_link_scheme',
		'type'  => 'text',
		'title' => 'App Schema'
	],
	[
		'id'    => 'mobile_app_android_package',
		'type'  => 'text',
		'title' => 'Android Package App'
	],
	[
		'id'    => 'mobile_app_ios_app_id',
		'type'  => 'text',
		'title' => 'IOS App Id'
	],
	[
		'id'    => 'mobile_app_deep_link_icon',
		'type'  => 'media',
		'title' => 'App Icon'
	],
	[
		'id'      => 'mobile_app_deep_link_title',
		'type'    => 'text',
		'title'   => __('Title', 'Wilcity-app-deep-link'),
		'default' => 'Wilcity App'
	],
	[
		'id'    => 'mobile_app_deep_link_title_text_color',
		'type'  => 'color',
		'title' => __('Title Text Color', 'Wilcity-app-deep-link'),
	],
	[
		'id'      => 'mobile_app_deep_link_desc',
		'type'    => 'textarea',
		'title'   => __('Description', 'Wilcity-app-deep-link'),
		'default' => 'Open In App'
	],
	[
		'id'    => 'mobile_app_deep_link_desc_text_color',
		'type'  => 'color',
		'title' => __('Description Text Color', 'Wilcity-app-deep-link'),
	],
	[
		'id'    => 'mobile_app_deep_link_desc_bg_color',
		'type'  => 'color',
		'title' => __('Description Background Color', 'Wilcity-app-deep-link'),
	],
	[
		'id'    => 'mobile_app_deep_link_icon',
		'type'  => 'media',
		'title' => 'Icon'
	],
	[
		'id'    => 'mobile_app_deep_link_bg_color',
		'type'  => 'color',
		'title' => __('Body Background Color', 'Wilcity-app-deep-link'),
	],
	[
		'id'     => 'mobile_app_deep_link_settings_close',
		'title'  => '',
		'type'   => 'section',
		'indent' => false
	],
];
