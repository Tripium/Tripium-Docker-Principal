<?php

namespace WooKit\Discount\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class DiscountCodeController
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST, 'coupons',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getCouponCodes'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function getCouponCodes(WP_REST_Request $oRequest): WP_REST_Response
    {
        $aItems = [];
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')->error(
                esc_html__('Forbidden', 'wookit'),
                403
            );
        }

        $aCoupons = get_posts([
            'posts_per_page' => 100,
            'orderby'        => 'post_date',
            'order'          => 'asc',
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => 'date_expires', // Check the start date field
                    'value'   => current_time('timestamp', 1), // Set today's date (note the similar format)
                    'compare' => '>=', // Return the ones greater than today's date
                ]
            ]
        ]);

        if (empty($aCoupons) || is_wp_error($aCoupons)) {
            return MessageFactory::factory('rest')->success(
                esc_html__('We found no coupon', 'wookit'),
                [
                    'items' => []
                ]
            );
        }

        /**
         * @var WP_Post $aCoupon
         */

        foreach ($aCoupons as $oCoupon) {
            $endsAt = date('Y-m-d H:i:s', get_post_meta($oCoupon->ID, 'date_expires', true));
            $aItems[] = [
                'code'        => $oCoupon->post_name,
                'description' => $oCoupon->post_excerpt,
                'endsAt'      => $endsAt,
                'id'          => $oCoupon->ID,
                'startsAt'    => $oCoupon->post_date,
                'status'      => 'ACTIVE'
            ];
        }

        return MessageFactory::factory('rest')->success(
            sprintf(esc_html__('We found %s items', 'wookit'), count($aItems)),
            [
                'items' => $aItems
            ]
        );
    }
}
