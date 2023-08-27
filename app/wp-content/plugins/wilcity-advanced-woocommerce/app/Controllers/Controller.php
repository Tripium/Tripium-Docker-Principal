<?php

namespace WilcityAdvancedProducts\Controllers;

use WilokeListingTools\Frontend\User;

class Controller
{
    public function isUsingWooCommerce()
    {
        return has_action('woocommerce_loaded ');
    }
    
    public function getProductAuthor($productID)
    {
        if (function_exists('dokan_get_vendor_by_product')) {
            return dokan_get_vendor_by_product($productID)->get_id();
        }
        
        return get_post_field('post_author', $productID);
    }
    
    public function getAdminOrderTrackingURL($oOrder)
    {
        if (function_exists('dokan_get_navigation_url')) {
            $orderURL = wp_nonce_url(
                add_query_arg(
                    [
                        'order_id' => dokan_get_prop($oOrder, 'id')
                    ],
                    dokan_get_navigation_url('orders')
                ), 'dokan_view_order'
            );
            
            $orderURL = str_replace('&amp;', '&', $orderURL);
        } else {
            $orderURL = add_query_arg(
                admin_url('post.php'),
                [
                    'post'   => $oOrder->get_id(),
                    'action' => 'edit'
                ]
            );
        }
        
        return $orderURL;
    }
    
    public function cleanMessage($aData)
    {
        $aData = wp_parse_args($aData, [
            'oOrder'               => '',
            'orderID'              => '',
            'aProducts'            => [],
            'customerID'           => '',
            'postAuthor'           => '',
            'shopOwnerPhoneNumber' => '',
            'customerPhoneNumber'  => '',
            'sendTo'               => 'customer'
        ]);
        
        if (!empty($aData['oOrder'])) {
            $customerName = $aData['oOrder']->get_formatted_billing_full_name();
        } else {
            $customerName = User::getField('display_name', $aData['customerID']);
        }
        
        $msg = str_replace(
            [
                '%orderID%',
                '%productName%',
                '%bookingInfo%',
                '%customerName%',
                '%shopOwnerName%',
                '%shopOwnerPhoneNumber%',
                '%customerPhoneNumber%',
            ],
            [
                $aData['orderID'],
                implode(',', $aData['aProducts']),
                implode(',', $aData['aProducts']),
                $customerName,
                User::getField('display_name', $aData['postAuthor']),
                $aData['shopOwnerPhoneNumber'],
                $aData['customerPhoneNumber']
            ],
            $aData['msg']
        );
        
        return apply_filters(
            'wilcity/filter/wilcity-advanced-woocommerce/app/controller/message',
            $msg,
            $aData
        );
    }
    
    public function parseOrderMsg($order)
    {
        $oOrder  = $order instanceof \WC_Order ? $order : new \WC_Order($order);
        $orderID = $oOrder->get_id();
        
        $aItems = $oOrder->get_items();
        
        $aBookingProducts  = [];
        $aStandardProducts = [];
        $aProductAuthors   = [];
        $aBookingIDs       = [];
        
        foreach ($aItems as $oItem) {
            $productID  = $oItem->get_product_id();
            $oProduct   = wc_get_product($productID);
            $postAuthor = get_post_field('post_author', $productID);
            if ($oProduct->is_type('booking')) {
                $aBookingIDs = \WC_Booking_Data_Store::get_booking_ids_from_order_id($orderID);
                
                if (is_array($aBookingIDs)) {
                    foreach ($aBookingIDs as $bookingID) {
                        $oBooking                        = get_wc_booking($bookingID);
                        $startDate                       = $oBooking->get_start_date();
                        $aBookingProducts[$postAuthor][] = get_the_title($productID).': '.
                                                           sprintf(esc_html__('Start Date %s',
                                                               'wilcity-advanced-woocommerce'),
                                                               $startDate);
                    }
                } else {
                    $oBooking                        = get_wc_booking($aBookingIDs);
                    $startDate                       = $oBooking->get_start_date();
                    $aBookingProducts[$postAuthor][] = get_the_title($productID).' - '.
                                                       sprintf(esc_html__('Start Date %s',
                                                           'wilcity-advanced-woocommerce'),
                                                           $startDate);
                }
            } else {
                $aStandardProducts[$postAuthor][] = get_the_title($productID);
            }
            
            if (!in_array($postAuthor, [$aProductAuthors])) {
                $aProductAuthors[] = $postAuthor;
            }
        }
        
        $aResponse = [
            'customerID' => $oOrder->get_user_id(),
            'authors'    => $aProductAuthors
        ];
        if (!empty($aStandardProducts)) {
            $aResponse['standard'] = [
                'aProducts' => $aStandardProducts
            ];
        }
        
        if (!empty($aBookingProducts)) {
            $aResponse['booking'] = [
                'aProducts' => $aBookingProducts,
                'ids'       => $aBookingIDs
            ];
        }
        
        return $aResponse;
    }
}
