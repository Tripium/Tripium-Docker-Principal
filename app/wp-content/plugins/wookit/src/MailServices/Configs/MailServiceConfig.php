<?php

$beforeTextLink    = 'Click';
$target            = '_blank';
$beforeServiceName = 'to learn how to set up ';
$textLink          = 'here';

$aServicesConfig = [
	'activecampaign'  => [
		'class'     => 'WooKit\MailServices\ActiveCampaign\Controllers\ActiveCampaignController',
		'guideLink' => MYSHOPKIT_LINK . 'mailservices/active-campaign',
		'label'     => 'Active Campaign',
	],
	'campaignmonitor' => [
		'class'     => 'WooKit\MailServices\CampaignMonitor\Controllers\CampaignMonitorController',
		'guideLink' => MYSHOPKIT_LINK . 'mailservices/campaign-monitor',
		'label'     => 'Campaign Monitor',
	],
	'getresponse'     => [
		'class'     => 'WooKit\MailServices\GetResponse\Controllers\GetResponseController',
		'guideLink' => MYSHOPKIT_LINK . 'mailservices/get-response',
		'label'     => 'Get Response',
	],
	'klaviyo'         => [
		'class'     => 'WooKit\MailServices\Klaviyo\Controllers\KlaviyoController',
		'guideLink' => MYSHOPKIT_LINK . 'mailservices/klaviyo',
		'label'     => 'Klaviyo',
	],
	'icontact'        => [
		'class'     => 'WooKit\MailServices\iContact\src\Controllers\iContactController',
		'guideLink' => MYSHOPKIT_LINK . 'mailservices/icontact',
		'label'     => 'iContact',
	],
	'mailchimp'       => [
		'class'     => 'WooKit\MailServices\MailChimp\Controllers\MailChimpController',
		'guideLink' => MYSHOPKIT_LINK . 'mailservices/mailchimp',
		'label'     => 'MailChimp',
	],
];

foreach ( $aServicesConfig as $campaign => $aServiceConfig ) {
	$description                                 = sprintf(
		'%s <a href="%s" target="%s"> %s </a> %s %s',
		$beforeTextLink,
		$aServiceConfig['guideLink'],
		$target,
		$textLink,
		$beforeServiceName,
		$aServiceConfig['label']
	);
	$aServicesConfig[ $campaign ]['description'] = $description;
}

return
	apply_filters( WOOKIT_HOOK_PREFIX . 'Filter/MailServices/Configs/MailServiceConfig', $aServicesConfig );
