<?php
$aTranslations = [
	"heading"         => esc_html__("Settings Banner", 'wiloke-notification-bar-wp'),
	"addNew"          => esc_html__("Add New", 'wiloke-notification-bar-wp'),
	"edit"            => esc_html__("Edit", 'wiloke-notification-bar-wp'),
	"delete"          => esc_html__("Delete", 'wiloke-notification-bar-wp'),
	"submit"          => esc_html__("Submit", 'wiloke-notification-bar-wp'),
	"upload"          => esc_html__("Upload", 'wiloke-notification-bar-wp'),
	"deleteTheBanner" => esc_html__("Delete The Banner", 'wiloke-notification-bar-wp'),
];
$aInitConfig = [
	[
		"name"        => "bannerName",
		"label"       => esc_html__("Banner Name", 'wiloke-notification-bar-wp'),
		"type"        => "text",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => 'Banner Name'
	],
	[
		"name"        => "description",
		"label"       => esc_html__("Description", 'wiloke-notification-bar-wp'),
		"type"        => "textarea",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Recusandae, tempora.'
	],
	[
		"name"        => "discountLabel",
		"label"       => esc_html__("Discount Name", 'wiloke-notification-bar-wp'),
		"type"        => "text",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => 'Up To'
	],
	[
		"name"        => "discount",
		"label"       => esc_html__("Discount Highlight", 'wiloke-notification-bar-wp'),
		"type"        => "text",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => '40%'
	],
	[
		"name"        => "nameButton",
		"label"       => esc_html__("Get Now Button Name", 'wiloke-notification-bar-wp'),
		"type"        => "text",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => 'Get Now'
	],
	[
		"name"        => "hrefButton",
		"label"       => esc_html__("Banner URL", 'wiloke-notification-bar-wp'),
		"type"        => "text",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => '#'
	],
	[
		"name"        => "hrefBlank",
		"label"       => esc_html__("Specifies where to open the linked document", 'wiloke-notification-bar-wp'),
		"type"        => "select",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"options"     => [["label" => esc_html__('Blank', 'wiloke-notification-bar-wp'),
		                   "value" => '_blank'],
		                  ["label" => esc_html__('Self', 'wiloke-notification-bar-wp'),
		                   "value" => '_self']],
		"value"       => '_self'
	],
	[
		"name"        => "campaignExpiryDate",
		"label"       => esc_html__("Campaign Expiry Date", 'wiloke-notification-bar-wp'),
		"type"        => "datePicker",
		"require"     => false,
		"placeholder" => "",
		"description" => "",
		"value"       => getdate()[0] * 1000
	],
	[
		'label'       => esc_html__('Inherit General Settings', 'wiloke-notification-bar-wp'),
		'name'        => 'isInheritGeneralSettings',
		'type'        => 'switch',
		"require"     => '',
		"placeholder" => "",
		"description" => "",
		'value'       => false
	],
	[
		"name"          => "backgroundType",
		"label"         => esc_html__("Background Type", 'wiloke-notification-bar-wp'),
		"type"          => "select",
		"require"       => "",
		"placeholder"   => "",
		"description"   => "",
		"options"       => [["label" => esc_html__('Upload Background Image', 'wiloke-notification-bar-wp'),
		                     "value" => "backgroundImage"],
		                    ["label" => esc_html__('Background Color', 'wiloke-notification-bar-wp'),
		                     "value" => "backgroundColor"]],
		"value"         => 'backgroundColor',
		"parentRequire" => ["isInheritGeneralSettings" => false],
	],
	[
		"name"          => "backgroundImage",
		"label"         => esc_html__("Upload Background Image", 'wiloke-notification-bar-wp'),
		"type"          => "upload",
		"require"       => true,
		"parentRequire" => ["backgroundType" => "backgroundImage", 'isInheritGeneralSettings' => false],
		"placeholder"   => "",
		"description"   => "",
		"value"         => WILOKE_NB_DIR_URL . 'assets/placeholderImage/1200x300.png'
	],
	[
		"name"          => "backgroundColor",
		"label"         => esc_html__("Background Color", 'wiloke-notification-bar-wp'),
		"type"          => "colorPicker",
		"require"       => true,
		"parentRequire" => ["backgroundType" => "backgroundColor", 'isInheritGeneralSettings' => false],
		"placeholder"   => "",
		"description"   => "",
		"value"         => '#ffffff'
	],
	[
		"name"          => "textColor",
		"label"         => esc_html__("Text Color Picker", 'wiloke-notification-bar-wp'),
		"type"          => "colorPicker",
		"require"       => true,
		"placeholder"   => "",
		"description"   => "",
		"value"         => "#0000ff",
		"parentRequire" => ["isInheritGeneralSettings" => false],
	],
	[
		"name"          => "accentColor",
		"label"         => esc_html__("Accent Color Picker", 'wiloke-notification-bar-wp'),
		"type"          => "colorPicker",
		"require"       => true,
		"placeholder"   => "",
		"description"   => "",
		"value"         => '#ff0000',
		"parentRequire" => ["isInheritGeneralSettings" => false]
	],
	[
		"name"          => "buttonColor",
		"label"         => esc_html__("Button Color Picker", 'wiloke-notification-bar-wp'),
		"type"          => "colorPicker",
		"require"       => true,
		"placeholder"   => "",
		"description"   => "",
		"value"         => '#ffffff',
		"parentRequire" => ["isInheritGeneralSettings" => false]
	],
	[
		"name"          => "logo",
		"label"         => esc_html__("Upload Logo", 'wiloke-notification-bar-wp'),
		"type"          => "upload",
		"require"       => true,
		"placeholder"   => "",
		"description"   => "",
		"value"         => WILOKE_NB_DIR_URL . 'assets/placeholderImage/100x100.png',
		"parentRequire" => ["isInheritGeneralSettings" => false]
	]
];
$aConfigCommon = [
	[
		'label'       => esc_html__('Banner Status', 'wiloke-notification-bar-wp'),
		'name'        => 'bannerStatus',
		'type'        => 'switch',
		"require"     => '',
		"placeholder" => "",
		"description" => "",
		'value'       => true
	],
	[
		"name"        => "backgroundType",
		"label"       => esc_html__("Background Type", 'wiloke-notification-bar-wp'),
		"type"        => "select",
		"require"     => "",
		"placeholder" => "",
		"description" => "",
		"options"     => [["label" => esc_html__('Upload Background Image', 'wiloke-notification-bar-wp'),
		                   "value" => "backgroundImage"],
		                  ["label" => esc_html__('Background Color', 'wiloke-notification-bar-wp'),
		                   "value" => "backgroundColor"]],
		"value"       => 'backgroundColor'
	],
	[
		"name"          => "backgroundImage",
		"label"         => esc_html__("Upload Background Image", 'wiloke-notification-bar-wp'),
		"type"          => "upload",
		"require"       => true,
		"parentRequire" => ["backgroundType" => "backgroundImage"],
		"placeholder"   => "",
		"description"   => "",
		"value"         => WILOKE_NB_DIR_URL . 'assets/placeholderImage/1200x300.png'
	],
	[
		"name"          => "backgroundColor",
		"label"         => esc_html__("Background Color", 'wiloke-notification-bar-wp'),
		"type"          => "colorPicker",
		"require"       => true,
		"parentRequire" => ["backgroundType" => "backgroundColor"],
		"placeholder"   => "",
		"description"   => "",
		"value"         => '#ffffff'
	],
	[
		"name"        => "autoplaySpeed",
		"label"       => esc_html__("Autoplay Speed(ms)", 'wiloke-notification-bar-wp'),
		"type"        => "text",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"variant"     => 'number',
		"value"       => 2000
	],
	[
		"name"        => "textColor",
		"label"       => esc_html__("Text Color Picker", 'wiloke-notification-bar-wp'),
		"type"        => "colorPicker",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => '#0000ff'
	],
	[
		"name"        => "accentColor",
		"label"       => esc_html__("Accent Color Picker", 'wiloke-notification-bar-wp'),
		"type"        => "colorPicker",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => '#ff0000'
	],
	[
		"name"        => "buttonColor",
		"label"       => esc_html__("Button Color Picker", 'wiloke-notification-bar-wp'),
		"type"        => "colorPicker",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => '#ffffff'
	],
	[
		"name"        => "logo",
		"label"       => esc_html__("Upload Logo", 'wiloke-notification-bar-wp'),
		"type"        => "upload",
		"require"     => true,
		"placeholder" => "",
		"description" => "",
		"value"       => WILOKE_NB_DIR_URL.'assets/placeholderImage/100x100.png'
	]
];
$aConfigAddCss = [
	[
		"name"        => "addCss",
		"label"       => esc_html__("Custom Css", 'wiloke-notification-bar-wp'),
		"type"        => "css",
		"require"     => false,
		"placeholder" => "",
		"description" => "",
		"value"       => ''
	]
];
return[
	'aTranslations'=>$aTranslations,
	'aInitConfig'=>$aInitConfig,
	'aConfigCommon'=>$aConfigCommon,
	'aConfigAddCss'=>$aConfigAddCss
];
