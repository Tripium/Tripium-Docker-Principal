<?php
function updateValueField($aConfigFields, $aValueData)
{
	$aDataField = [];
	foreach ($aConfigFields as $aField) {
		$aField['value'] = isset($aValueData[$aField['name']]) ? $aValueData[$aField['name']] : '';
		$aDataField[] = $aField;
	}

	return $aDataField;
}

function setupDataConfig($data, $config)
{
	return [
		'WilokeNotificationBarTabs'         => [
			[
				"name"   => 'generalSettings',
				"label"  => esc_html__('General Settings', 'wiloke-notification-bar-wp'),
				"fields" => $data['aCommonBanner']
			],
			[
				"name"   => 'slideItemSettings',
				"label"  => esc_html__('Slider Item Settings', 'wiloke-notification-bar-wp'),
				"fields" => $config,
				"data"   => $data['aFields']
			],
			[
				"name"   => 'advancedSettings',
				"label"  => esc_html__('Advanced Settings', 'wiloke-notification-bar-wp'),
				"fields" => $data['aCommonBannerCSS']
			]
		],
		'WilokeNotificationBarTranslations' => $data['aTranslations']
	];
}
