<?php

namespace WooKit\General\Controllers;

use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Message\MessageDefinition;
use WP_REST_Request;

class GeneralController
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouter']);
    }

    public function registerRouter()
    {
        register_rest_route(
            WOOKIT_REST,
            'campaigns/status',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'getCampaignsStatus'],
                'permission_callback' => '__return_true'
            ]
        );
        register_rest_route(
            WOOKIT_REST,
            'shop-info',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'getShopInfo'],
                'permission_callback' => '__return_true'
            ]
        );
    }

    public function getCampaignsStatus(WP_REST_Request $oRequest)
    {
        try {
            if (!is_user_logged_in()) {
                return MessageFactory::factory('rest')
                    ->error(MessageDefinition::youMustLogin(), 401);
            }
            $aResponse = apply_filters(WOOKIT_PREFIX .
                'Filter/General/Controllers/GeneralController/getCampaignsStatus',
                [
                    'hasPopup'    => false,
                    'hasSmartbar' => false,
                    'hasSlidein'  => false,

                ],
                [
                    'userID' => get_current_user_id()
                ]
            );
            return MessageFactory::factory('rest')->success('Campaigns Status',
                [
                    'hasPopup'    => $aResponse['hasPopup'],
                    'hasSmartbar' => $aResponse['hasSmartbar'],
                    'hasSlidein'  => $aResponse['hasSlidein'],
                ]
            );
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
        }
    }

    public function getShopInfo(WP_REST_Request $oRequest)
    {
        try {
            if (!is_user_logged_in()) {
                return MessageFactory::factory('rest')
                    ->error(MessageDefinition::youMustLogin(), 401);
            }

            return MessageFactory::factory('rest')->success('Shop Info',
                [
                    'locale'       => str_replace('_', '-', get_locale()),
                    'currency'     => !function_exists('get_woocommerce_currency') ? 'USD' : get_woocommerce_currency(),
                    'site'         => get_bloginfo('name'),
                    'linkDiscount' => add_query_arg([
                        'post_type' => 'shop_coupon'
                    ], admin_url('post-new.php')),
                ]
            );
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
        }
    }
}
