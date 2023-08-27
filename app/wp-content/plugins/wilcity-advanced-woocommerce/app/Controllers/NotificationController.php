<?php

namespace WilcityAdvancedProducts\Controllers;

use WilokeListingTools\Framework\Helpers\FileSystem;
use WilokeListingTools\Framework\Helpers\Firebase;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\NotificationsModel;

class NotificationController extends Controller
{
    private $pushNotificationCenter = 'https://exp.host/--/api/v2/push/send';
    
    public function __construct()
    {
        add_filter('wilcity/filter/wiloke-listing-tools/configs/push-notifications', [$this, 'addNewNotifications']);
        
        add_action(
            'wilcity/wilcity-mobile-app/app/controller/customer-sms-message-controller/sent-message',
            [$this, 'pushWebNotificationToCustomer']
        );
        
        add_action(
            'wilcity/wilcity-mobile-app/app/controller/customer-sms-message-controller/sent-message',
            [$this, 'pushAppNotificationToCustomer']
        );
        
        add_action(
            'wilcity/wilcity-mobile-app/app/controller/shop-owner-sms-message-controller/sent-message',
            [$this, 'pushWebNotificationToShopOwner']
        );
        
        add_action(
            'wilcity/wilcity-mobile-app/app/controller/shop-owner-sms-message-controller/sent-message',
            [$this, 'pushAppNotificationToShopOwner']
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/purchased_standard_product',
            [$this, 'purchasedStandardProductNotification'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/purchased_booking_product',
            [$this, 'purchasedBookingProductNotification'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/placed_order_booking_product',
            [$this, 'placedOrderBookingNotification'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/placed_order_standard_product',
            [$this, 'placedOrderStandardNotification'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/received_order_standard_product',
            [$this, 'receivedOrderStandardProduct'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/received_order_booking_product',
            [$this, 'receivedOrderBookingProduct'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/received_purchased_standard_product',
            [$this, 'receivedPurchasedOrderStandardProduct'],
            10,
            2
        );
        
        add_filter(
            'wilcity/wiloke-listing-tools/get-notification/received_purchased_booking_product',
            [$this, 'receivedPurchasedOrderBookingProduct'],
            10,
            2
        );
        
        add_filter(
            'wilcity/filter/wilcity-mobile-app/controller/firebase/push-notification-controller/is-send-someone-purchased-your-product',
            [$this, 'turnOffSendSomeonePurchasedYourProduct']
        );
    }
    
    public function addNewNotifications($aNotifications)
    {
        $aNotifications['customers']['placedOrderStandardProduct'] = [
            'title'  => 'Placed an order of a standard product',
            'desc'   => 'When you placed an order of a product successfully, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'Your order %orderID% has been placed successfully. Hotline: %shopOwnerPhoneNumber%',
        ];
        
        $aNotifications['customers']['purchasedStandardProduct'] = [
            'title'  => 'Purchased a standard product',
            'desc'   => 'When you purchased a product successfully, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'Your order %orderID% has been processed successfully. If you have any question, feel free contact us on %shopOwnerPhoneNumber%',
        ];
        
        $aNotifications['customers']['placedOrderBookingProduct'] = [
            'title'  => 'Placed an order of booking product successfully',
            'desc'   => 'When you placed an order of a booking product successfully, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'Thank for using our service, We will contact you shortly! %bookingInfo%. Hotline: %shopOwnerPhoneNumber%'
        ];
        
        $aNotifications['customers']['purchasedBookingProduct'] = [
            'title'  => 'Booked Product',
            'desc'   => 'When you booked a product successfully, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'Your order %orderID% has been processed successfully. %bookingInfo%. If you have any question, feel free contact us on %shopOwnerPhoneNumber%',
        ];
        
        $aNotifications['customers']['receivedPurchasedStandardProduct'] = [
            'title'  => 'Received a complete order of your standard product',
            'desc'   => 'When a customer purchased a standard product successfully, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'Congratulations! You made a sale on %productName%. Order ID: %orderID%. Customer Phone: %customerPhoneNumber%',
        ];
        
        $aNotifications['customers']['receivedOrderStandardProduct'] = [
            'title'  => 'Received an On-Hold Order of your standard product',
            'desc'   => 'When a customer placed an order of your standard product, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'You received an order from %customerName%. Order ID: %orderID%, Products: %productName%, Customer Phone: %customerPhoneNumber%'
        ];
        
        $aNotifications['customers']['receivedOrderBookingProduct'] = [
            'title'  => 'Received an On-Hold order of your booking product',
            'desc'   => 'When a customer placed an order of your product successfully, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => '%customerName% placed an order on %productName%. Order ID: #%orderID%, Customer Phone Number: %customerPhoneNumber%',
        ];
        
        $aNotifications['customers']['receivedPurchasedBookingProduct'] = [
            'title'  => 'Received a complete order of your booking product',
            'desc'   => 'When you received a complete order of your product, a notification will be sent to you.',
            'status' => 'on',
            'msg'    => 'You received an booking order #%orderID% from %customerName%. %customerPhoneNumber%',
        ];
        
        return $aNotifications;
    }
    
    public function turnOffSendSomeonePurchasedYourProduct()
    {
        return false;
    }
    
    /**
     * @param $oInfo
     * @param $productType
     * @param $notificationType
     * @param $notificationKey
     * @param $themeOptionKey
     *
     * @return array
     */
    protected function renderWebNotification($oInfo, $productType, $notificationType, $notificationKey, $themeOptionKey)
    {
        $orderID       = $oInfo->objectID;
        $oOrder        = wc_get_order($orderID);
        $aData         = $this->parseOrderMsg($oOrder);
        $aNotification = [];
        
        if (!isset($aData[$productType]) && empty($oOrder)) {
            $aNotification = [
                'featuredImg' => '',
                'content'     => esc_html__('This order has been deleted', 'wilcity-advanced-woocommerce'),
                'link'        => '#',
                'time'        => Time::timeFromNow(strtotime($oInfo->date)),
                'type'        => $notificationType,
                'ID'          => absint($oInfo->ID)
            ];
        } else {
            $customerPhoneNumber = $oOrder->get_billing_phone();
            $placeHolderMsg      = Firebase::getCustomerMsg($notificationKey);
            if (empty($placeHolderMsg)) {
                $placeHolderMsg = \WilokeThemeOptions::getOptionDetail($themeOptionKey);
            }
            
            if (!empty($placeHolderMsg)) {
                foreach ($aData['authors'] as $postAuthor) {
                    if (!isset($aData[$productType]['aProducts'][$postAuthor])) {
                        continue;
                    }
                    $shopOwnerPhoneNumber = User::getPhone($postAuthor);
                    
                    $msg = $this->cleanMessage(
                        [
                            'oOrder'               => $oOrder,
                            'orderID'              => $orderID,
                            'customerID'           => $oOrder->get_user_id(),
                            'msg'                  => $placeHolderMsg,
                            'customerPhoneNumber'  => $customerPhoneNumber,
                            'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                            'postAuthor'           => $postAuthor
                        ]
                    );
                    
                    $aNotification = [
                        'featuredImg' => User::getAvatar($oOrder->get_user_id()),
                        'content'     => $msg,
                        'link'        => $this->getAdminOrderTrackingURL($oOrder),
                        'time'        => Time::timeFromNow(strtotime($oInfo->date)),
                        'type'        => $notificationType,
                        'ID'          => absint($oInfo->ID)
                    ];
                }
            }
        }
        
        return $aNotification;
    }
    
    public function receivedPurchasedOrderBookingProduct($oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'booking',
            'received_purchased_booking_product',
            'receivedPurchasedBookingProduct',
            'wilcity_booking_purchased_product_message'
        );
    }
    
    public function placedOrderBookingNotification($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'booking',
            'placed_order_booking_product',
            'placedOrderBookingProduct',
            'wilcity_booking_placed_order_product_message'
        );
    }
    
    public function placedOrderStandardNotification($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'standard',
            'purchased_standard_product',
            'placedOrderStandardProduct',
            'wilcity_standard_placed_order_product_message'
        );
    }
    
    public function purchasedStandardProductNotification($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'standard',
            'purchased_standard_product',
            'purchasedStandardProduct',
            'wilcity_standard_purchased_product_message'
        );
    }
    
    public function purchasedBookingProductNotification($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'booking',
            'purchased_standard_product',
            'purchasedBookingProduct',
            'wilcity_booking_purchased_product_message'
        );
    }
    
    public function receivedPurchasedOrderStandardProduct($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'standard',
            'received_purchased_standard_product',
            'receivedPurchasedStandardProduct',
            'wilcity_standard_received_order_product_message'
        );
    }
    
    public function receivedOrderBookingProduct($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'booking',
            'received_order_booking_product',
            'receivedOrderBookingProduct',
            'wilcity_booking_received_placed_order_product_message'
        );
    }
    
    public function receivedOrderStandardProduct($aNotification, $oInfo)
    {
        return $this->renderWebNotification(
            $oInfo,
            'standard',
            'received_order_standard_product',
            'receivedOrderStandardProduct',
            'wilcity_standard_received_order_product_message'
        );
    }
    
    protected function pushNotificationToUser($aInfo, $userID, $notificationKey)
    {
        $oOrder = $aInfo['oOrder'];
        if (!Firebase::isCustomerEnable($notificationKey, $userID)) {
            return new \WP_Error('disabled', 'Notification is disabled');
        }
        
        // App Notification
        if (empty(Firebase::getDeviceToken())) {
            return new \WP_Error('missing', 'Missing Customer Device Token');
        }
        
        $msg = Firebase::getCustomerMsg($notificationKey);
        
        if (!empty($msg)) {
            $msg = $this->cleanMessage(
                [
                    'oOrder'     => $oOrder,
                    'orderID'    => $oOrder->get_id(),
                    'customerID' => $oOrder->get_user_id(),
                    'msg'        => $msg,
                    'postAuthor' => $aInfo['postAuthor'],
                    'aProducts'  => $aInfo['aProducts']
                ]
            );
        } else {
            $msg = $aInfo['msg'];
        }
        
        $aBody['to']    = Firebase::getDeviceToken();
        $aBody['sound'] = 'default';
        $aBody['body']  = $msg;
        
        $oResponse = wp_remote_post($this->pushNotificationCenter, [
            'headers' => [
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
                'Accept-encoding' => 'gzip, deflate'
            ],
            'body'    => json_encode($aBody)
        ]);
        
        Firebase::resetInfo();
        
        return $oResponse;
    }
    
    public function pushAppNotificationToShopOwner($aInfo)
    {
        $oOrder  = $aInfo['oOrder'];
        $metaKey = 'wilcity_sent_shop_owner_notification_to_app_'.$oOrder->get_status();
        
        if (!$aInfo['isFocus'] && $oOrder->get_meta($metaKey, true)) {
            return false;
        }
        
        if ($oOrder->get_status() != 'on-hold') {
            if ($aInfo['type'] == 'booking') {
                $key = 'receivedPurchasedBookingProduct';
            } else {
                $key = 'receivedPurchasedStandardProduct';
            }
        } else {
            if ($aInfo['type'] == 'booking') {
                $key = 'receivedOrderBookingProduct';
            } else {
                $key = 'receivedOrderStandardProduct';
            }
        }
        
        $oResponse = $this->pushNotificationToUser($aInfo, $aInfo['postAuthor'], $key);
        
        if (!is_wp_error($oResponse)) {
            $oOrder->update_meta_data(
                $metaKey,
                current_time('timestamp', true)
            );
            
            $oOrder->add_order_note(
                esc_html__('A Notification has been sent to shop owner (App)', 'wilcity-advanced-woocommerce')
            );
        }
    }
    
    public function pushAppNotificationToCustomer($aInfo)
    {
        $oOrder  = $aInfo['oOrder'];
        $metaKey = 'wilcity_sent_customer_notification_to_app_'.$oOrder->get_status();
        
        if (!$aInfo['isFocus'] && $oOrder->get_meta($metaKey, true)) {
            return false;
        }
        
        if ($oOrder->get_status() != 'on-hold') {
            if ($aInfo['type'] == 'booking') {
                $key = 'purchasedBookingProduct';
            } else {
                $key = 'purchasedStandardProduct';
            }
        } else {
            if ($aInfo['type'] == 'booking') {
                $key = 'placedOrderBookingProduct';
            } else {
                $key = 'placedOrderStandardProduct';
            }
        }
        
        $oResponse = $this->pushNotificationToUser($aInfo, $oOrder->get_user_id(), $key);
        
        if (!is_wp_error($oResponse)) {
            $oOrder->update_meta_data(
                $metaKey,
                current_time('timestamp', true)
            );
            
            $oOrder->add_order_note(
                esc_html__('A Notification has been sent to customer (App)', 'wilcity-advanced-woocommerce')
            );
        }
    }
    
    public function pushWebNotificationToShopOwner($aInfo)
    {
        $oOrder = $aInfo['oOrder'];
        
        if (!$aInfo['isFocus'] &&
            $oOrder->get_meta('wilcity_sent_show_owner_web_notification_'.$oOrder->get_status(), true)
        ) {
            return false;
        }
        
        if ($oOrder->get_status() != 'on-hold') {
            if ($aInfo['type'] == 'booking') {
                $key  = 'receivedPurchasedBookingProduct';
                $type = 'received_purchased_booking_product';
            } else {
                $key  = 'receivedPurchasedStandardProduct';
                $type = 'received_purchased_standard_product';
            }
        } else {
            if ($aInfo['type'] == 'booking') {
                $key  = 'receivedOrderBookingProduct';
                $type = 'received_order_booking_product';
            } else {
                $key  = 'receivedOrderStandardProduct';
                $type = 'received_order_standard_product';
            }
        }
        
        if (Firebase::isCustomerEnable($key, $aInfo['postAuthor'])) {
            $status = NotificationsModel::add(
                $aInfo['postAuthor'],
                $type,
                $oOrder->get_id()
            );
            
            if ($status) {
                $oOrder->update_meta_data(
                    'wilcity_sent_show_owner_web_notification_'.$oOrder->get_status(),
                    current_time('timestamp', true)
                );
                
                $oOrder->add_order_note(
                    esc_html__('A Notification has been sent to Shop Owner (Web)', 'wilcity-advanced-woocommerce')
                );
            }
        }
    }
    
    public function pushWebNotificationToCustomer($aInfo)
    {
        $oOrder = $aInfo['oOrder'];
        
        if (!$aInfo['isFocus'] &&
            $oOrder->get_meta('wilcity_sent_customer_web_notification_'.$oOrder->get_status(), true)
        ) {
            return false;
        }
        
        if ($oOrder->get_status() != 'on-hold') {
            if ($aInfo['type'] == 'booking') {
                $key  = 'purchasedBookingProduct';
                $type = 'purchased_booking_product';
            } else {
                $key  = 'purchasedStandardProduct';
                $type = 'purchased_standard_product';
            }
        } else {
            if ($aInfo['type'] == 'booking') {
                $key  = 'placedOrderBookingProduct';
                $type = 'placed_order_booking_product';
            } else {
                $key  = 'placedOrderStandardProduct';
                $type = 'placed_order_standard_product';
            }
        }
        
        if (Firebase::isCustomerEnable($key, $oOrder->get_user_id())) {
            $status = NotificationsModel::add(
                $oOrder->get_user_id(),
                $type,
                $oOrder->get_id()
            );
            
            if ($status) {
                $oOrder->update_meta_data(
                    'wilcity_sent_customer_web_notification_'.$oOrder->get_status(),
                    current_time('timestamp', true)
                );
                
                $oOrder->add_order_note(
                    esc_html__('A Notification has been sent to customer (Web)', 'wilcity-advanced-woocommerce')
                );
            }
        }
    }
}
