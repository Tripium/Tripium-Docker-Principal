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
    'slidein_general_settings_section' => [
        'id'           => 'slidein_general_settings_section',
        'title'        => esc_html__('Slidein General Settings', 'wookit'),
        'object_types' => [AutoPrefix::namePrefix('slidein')],
        'fields'       => [
            'config'         => [
                'name'       => esc_html__('Slidein Config', 'wookit'),
                'save_field' => false,
                'id'         => 'config',
                'type'       => 'textarea',
                //				'default_cb' => ['MyShopKit\Popup\Controllers\PopupAPIController', 'getConfig']
            ],
            'showOnPageMode' => [
                'name'             => esc_html__('Show On Page Mode', 'wookit'),
                'id'               => 'showOnPageMode',
                'type'             => 'select',
                'default'          => $aDefaultShowOnPageMode,
                'save_field'       => false,
                'show_option_none' => true,
                'options'          => [
                    'all'             => esc_html__('All Templates', 'wookit'),
                    'specified_pages' => esc_html__('Specified Pages', 'wookit'),
                ],
            ],
            'showOnPage'     => [
                'name'       => esc_html__('Display Templates', 'wookit'),
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
