<?php

use WooKit\Shared\App;
use WooKit\Shared\AutoPrefix;

$aDefaultTemplate = [];
$aDefaultShowOnPageMode = '';

if (isset($_GET['post']) && ($_GET['action'] === 'edit')) {
    $aDefaultTemplate = get_post_meta($_GET['post'], AutoPrefix::namePrefix('showOnPage'));
    $aDefaultShowOnPageMode = get_post_meta($_GET['post'], AutoPrefix::namePrefix('showOnPageMode'), true);
}
$aOptionTemplates = App::get('TemplateMeta');

return [
    'popup_general_settings_section' => [
        'id'           => 'popup_general_settings_section',
        'title'        => esc_html__('Popup General Settings', 'wookit'),
        'object_types' => [AutoPrefix::namePrefix('popup')],
        'fields'       => [
            'config'         => [
                'name'       => esc_html__('Popup Config', 'wookit'),
                'save_field' => false,
                'id'         => 'config',
                'type'       => 'textarea',
                //				'default_cb' => ['WooKit\Popup\Controllers\PopupAPIController', 'getConfig']
            ],
            'showOnPageMode' => [
                'name'             => esc_html__('Show On Page Mode', 'myshopkit'),
                'id'               => 'showOnPageMode',
                'type'             => 'select',
                'default'          => $aDefaultShowOnPageMode,
                'save_field'       => false,
                'show_option_none' => true,
                'options'          => [
                    'all'             => esc_html__('All Templates', 'myshopkit'),
                    'specified_pages' => esc_html__('Specified Pages', 'myshopkit'),
                ],
            ],
            'showOnPage'     => [
                'name'       => esc_html__('Display Templates', 'myshopkit'),
                'id'         => 'showOnPage',
                'type'       => 'multicheck',
                'save_field' => false,
                'multiple'   => true,
                'default'    => $aDefaultTemplate,
                'options'    => $aOptionTemplates
            ],
        ]
    ]
];
