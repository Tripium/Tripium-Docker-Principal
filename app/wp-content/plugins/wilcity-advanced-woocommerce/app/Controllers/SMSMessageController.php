<?php

namespace WilcityAdvancedProducts\Controllers;

use WILCITY_APP\Controllers\SMS\SMSFactory;
use WILCITY_APP\Controllers\SMS\TwilioSMS;
use WilokeListingTools\Frontend\User;

class SMSMessageController extends Controller
{
    public function __construct()
    {
        //        add_filter('wilcity/theme-options/configurations', [$this, 'addThemeOptionSettings']);
        //        add_filter('wilcity/filter/wilcity-mobile-app/configs/twiliooptions', [$this, 'woocommerceProductMessages']);
        add_action('woocommerce_order_status_changed', [$this, 'sendSMSAfterOrderChanged'], 10, 3);
        add_action('woocommerce_order_actions', [$this, 'addSendSMSMessageAction']);
        add_action(
          'woocommerce_order_action_wilcity_send_sms_message_to_customer',
          [$this, 'sendSMSMessageToCustomerDirectly'],
          10,
          1
        );
        add_action(
          'woocommerce_order_action_wilcity_send_sms_message_to_shop_owner',
          [$this, 'sendSMSMessageToShopOwnerDirectly'],
          10,
          1
        );
    }

    public function sendSMSMessageToCustomerDirectly($oOrder)
    {
        $this->sendSMS($oOrder, true, 'customer');
    }

    public function sendSMSMessageToShopOwnerDirectly($oOrder)
    {
        $this->sendSMS($oOrder, true, 'shop_owner');
    }

    public function addSendSMSMessageAction($aActions)
    {
        // add "mark printed" custom action
        $aActions['wilcity_send_sms_message_to_customer']   =
          esc_html__('Wilcity Resend SMS Message To Customer', 'wilcity-advanced-woocommerce');
        $aActions['wilcity_send_sms_message_to_shop_owner'] =
          esc_html__('Wilcity Resend SMS Message To Shop Owner', 'wilcity-advanced-woocommerce');

        return $aActions;
    }

    public function sendSMSAfterOrderChanged($orderID, $from, $to)
    {
        if (in_array($to, ['on-hold', 'processing', 'completed'])) {
            $this->sendSMS($orderID);
        }
    }

    protected function getAllowedCountry()
    {
        $allowedCountryMode = get_option('woocommerce_allowed_countries');
        if ($allowedCountryMode != 'all' && $allowedCountryMode != 'all_except') {
            $aAllowedCountries = WC()->countries->get_allowed_countries();
            if (!empty($aAllowedCountries)) {
                $aAllowedCountries = array_keys($aAllowedCountries);

                return $aAllowedCountries[0];
            }
        }

        return false;
    }

    protected function getStoreCountry($userID)
    {
        if (function_exists('dokan_get_store_info')) {
            $aDokanSettings = dokan_get_store_info($userID);
            if (
              isset($aDokanSettings['dokan_store_address']) &&
              isset($aDokanSettings['dokan_store_address']['settings'])
            ) {
                return $aDokanSettings['dokan_store_address'];
            }
        }

        $oUser = User::getUserData($userID);

        if (!empty($oUser->billing_country)) {
            $billingCountry = $oUser->billing_country;
        } else {
            $billingCountry = $this->getAllowedCountry();
        }

        return $billingCountry;
    }

    protected function getCustomerCountry(\WC_Order $oOrder)
    {
        $country = $oOrder->get_billing_country();
        if (!empty($country)) {
            return $country;
        }

        $oUser = User::getUserData($oOrder->get_customer_id());

        if (!empty($oUser->billing_country)) {
            $billingCountry = $oUser->billing_country;
        } else {
            $billingCountry = $this->getAllowedCountry();
        }

        return $billingCountry;
    }

    public function sendSMSToShopOwner(\WC_Order $oOrder, $isFocus = false)
    {
        if (!class_exists('WILCITY_APP\Controllers\SMS\SMSFactory')) {
            return false;
        }

        $storeSentKey = 'wilcity_sent_shop_owner_sms_'.$oOrder->get_status();
        if (!$isFocus) {
            if ($oOrder->get_meta($storeSentKey, true)) {
                $isSendingSMSToStandard = false;
                $isSendingSMSToBooking  = false;
            }
        }

        if (!isset($isSendingSMSToStandard)) {
            $isSendingSMSToStandard = \WilokeThemeOptions::isEnable('wilcity_toggle_send_sms_to_standard_products');
            $isSendingSMSToBooking  = \WilokeThemeOptions::isEnable('wilcity_toggle_send_sms_to_booking_products');
        }

        if ($oOrder->get_status() == 'on-hold') {
            $standardKey = 'wilcity_standard_received_order_product_message';
            $bookingKey  = 'wilcity_booking_received_placed_order_product_message';
        } else {
            $standardKey = 'wilcity_standard_received_purchased_product_message';
            $bookingKey  = 'wilcity_booking_received_purchased_product_message';
        }

        $aData               = $this->parseOrderMsg($oOrder);
        $sentMsg             = false;
        $customerPhoneNumber = $oOrder->get_billing_phone();

        if (empty($customerPhoneNumber)) {
            $customerPhoneNumber = User::getPhone($oOrder->get_id());
        }

        if (isset($aData['standard']) && !empty($aData['standard']['aProducts'])) {
            $msg = \WilokeThemeOptions::getOptionDetail($standardKey);

            foreach ($aData['authors'] as $postAuthor) {
                if (!isset($aData['standard']['aProducts'][$postAuthor])) {
                    continue;
                }

                $shopOwnerPhoneNumber = User::getPhone($postAuthor);

                if ($isSendingSMSToStandard) {
                    $msg = $this->cleanMessage([
                      'orderID'              => $oOrder->get_id(),
                      'customerID'           => $oOrder->get_user_id(),
                      'oOrder'               => $oOrder,
                      'msg'                  => $msg,
                      'customerPhoneNumber'  => $customerPhoneNumber,
                      'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                      'postAuthor'           => $postAuthor,
                      'aProducts'            => $aData['standard']['aProducts'][$postAuthor]
                    ]);

                    if (!empty($msg)) {
                        try {
                            $oService = SMSFactory::getService([
                              'userID'      => $postAuthor,
                              'msg'         => $msg,
                              'phoneNumber' => $shopOwnerPhoneNumber,
                              'country'     => $this->getStoreCountry($postAuthor)
                            ]);
                            $oService->send();
                            $sentMsg = true;

                            $oOrder->update_meta_data($storeSentKey, current_time('timestamp', true));

                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'Sent a SMS message to shop owner %s %s on %s at %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $postAuthor),
                                $oService->getSendTo(),
                                date_i18n('Y-m-d', current_time('timestamp')),
                                date_i18n('h-i A', current_time('timestamp'))
                              )
                            );
                        } catch (\Exception $oException) {
                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'We could not send a message to Shop Owner %s. Reason: %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $postAuthor),
                                $oException->getMessage()
                              )
                            );
                            $sentMsg = false;
                        }
                    }
                }

                do_action(
                  'wilcity/wilcity-advanced-woocommerce/app/controller/shop-owner-sms-message-controller/sent-message',
                  [
                    'type'                 => 'standard',
                    'oOrder'               => $oOrder,
                    'msg'                  => $msg,
                    'postAuthor'           => $postAuthor,
                    'customerPhoneNumber'  => $customerPhoneNumber,
                    'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                    'aProducts'            => $aData['standard']['aProducts'][$postAuthor],
                    'isSent'               => $sentMsg,
                    'isFocus'              => $isFocus
                  ]
                );
            }
        }

        if (isset($aData['booking']) && !empty($aData['booking']['aProducts'])) {
            $msg = \WilokeThemeOptions::getOptionDetail($bookingKey);
            foreach ($aData['authors'] as $postAuthor) {
                if (!isset($aData['booking']['aProducts'][$postAuthor])) {
                    continue;
                }

                $shopOwnerPhoneNumber = User::getPhone($postAuthor);

                if ($isSendingSMSToBooking) {
                    $msg = $this->cleanMessage([
                      'orderID'              => $oOrder->get_id(),
                      'customerID'           => $oOrder->get_user_id(),
                      'oOrder'               => $oOrder,
                      'msg'                  => $msg,
                      'customerPhoneNumber'  => $customerPhoneNumber,
                      'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                      'postAuthor'           => $postAuthor,
                      'aProducts'            => $aData['booking']['aProducts'][$postAuthor]
                    ]);

                    if (!empty($msg)) {
                        try {
                            $oService = SMSFactory::getService([
                              'userID'      => $postAuthor,
                              'msg'         => $msg,
                              'phoneNumber' => $shopOwnerPhoneNumber,
                              'country'     => $this->getStoreCountry($postAuthor)
                            ]);
                            $oService->send();
                            $sentMsg = true;
                            $oOrder->update_meta_data($storeSentKey, current_time('timestamp', true));

                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'Sent a SMS message to Shop Owner %s %s on %s at %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $oOrder->get_user_id()),
                                $oService->getSendTo(),
                                date_i18n('Y-m-d', current_time('timestamp')),
                                date_i18n('h-i A', current_time('timestamp'))
                              )
                            );
                        } catch (\Exception $oException) {
                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'We could not send a message to Shop Owner %s. Reason: %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $postAuthor),
                                $oException->getMessage()
                              )
                            );
                            $sentMsg = false;
                        }
                    }
                }

                do_action(
                  'wilcity/wilcity-advanced-woocommerce/app/controller/shop-owner-sms-message-controller/sent-message',
                  [
                    'type'                 => 'booking',
                    'oOrder'               => $oOrder,
                    'msg'                  => $msg,
                    'postAuthor'           => $postAuthor,
                    'customerPhoneNumber'  => $customerPhoneNumber,
                    'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                    'aProducts'            => $aData['booking']['aProducts'][$postAuthor],
                    'isSent'               => $sentMsg,
                    'isFocus'              => $isFocus
                  ]
                );
            }
        }
    }

    public function sendSMSToCustomer(\WC_Order $oOrder, $isFocus = false)
    {
	    if (!class_exists('WILCITY_APP\Controllers\SMS\SMSFactory')) {
		    return false;
	    }

        $storeSentKey = 'wilcity_sent_customer_sms_'.$oOrder->get_status();
        if (!$isFocus) {
            if ($oOrder->get_meta($storeSentKey, true)) {
                $isSendingSMSToStandard = false;
                $isSendingSMSToBooking  = false;
            }
        }

        if (!isset($isSendingSMSToStandard)) {
            $isSendingSMSToStandard = \WilokeThemeOptions::isEnable('wilcity_toggle_send_sms_to_standard_products');
            $isSendingSMSToBooking  = \WilokeThemeOptions::isEnable('wilcity_toggle_send_sms_to_booking_products');
        }

        if ($oOrder->get_status() == 'on-hold') {
            $standardKey = 'wilcity_standard_placed_order_product_message';
            $bookingKey  = 'wilcity_booking_placed_order_product_message';
        } else {
            $standardKey = 'wilcity_standard_purchased_product_message';
            $bookingKey  = 'wilcity_booking_purchased_product_message';
        }

        $aData               = $this->parseOrderMsg($oOrder);
        $sentMsg             = false;
        $customerCountry     = $this->getCustomerCountry($oOrder);
        $customerPhoneNumber = $oOrder->get_billing_phone();

        if (isset($aData['standard']) && !empty($aData['standard']['aProducts'])) {
            $msg = \WilokeThemeOptions::getOptionDetail($standardKey);
            foreach ($aData['authors'] as $postAuthor) {
                if (!isset($aData['standard']['aProducts'][$postAuthor])) {
                    continue;
                }
                $shopOwnerPhoneNumber = User::getPhone($postAuthor);

                if ($isSendingSMSToStandard) {
                    $msg = $this->cleanMessage(
                      [
                        'oOrder'               => $oOrder,
                        'orderID'              => $oOrder->get_id(),
                        'customerID'           => $oOrder->get_customer_id(),
                        'msg'                  => $msg,
                        'customerPhoneNumber'  => $customerPhoneNumber,
                        'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                        'postAuthor'           => $postAuthor,
                        'aProducts'            => $aData['standard']['aProducts'][$postAuthor]
                      ]
                    );

                    if (!empty($msg)) {
                        try {
                            $oService = SMSFactory::getService([
                              'userID'      => $oOrder->get_customer_id(),
                              'msg'         => $msg,
                              'phoneNumber' => $customerPhoneNumber,
                              'country'     => $customerCountry
                            ]);
                            $oService->send();
                            $sentMsg = true;

                            $oOrder->update_meta_data(
                              $storeSentKey,
                              current_time('timestamp', true)
                            );

                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'Sent a SMS message to Customer %s %s on %s at %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $oOrder->get_user_id()),
                                $oService->getSendTo(),
                                date_i18n('Y-m-d', current_time('timestamp')),
                                date_i18n('h-i A', current_time('timestamp'))
                              )
                            );
                        } catch (\Exception $oException) {
                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'We could not send a message to Customer %s %s. Reason %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $oOrder->get_user_id()),
                                $customerPhoneNumber,
                                $oException->getMessage()
                              )
                            );
                            $sentMsg = false;
                        }
                    }
                }

                do_action(
                  'wilcity/wilcity-advanced-woocommerce/app/controller/customer-sms-message-controller/sent-message',
                  [
                    'type'                 => 'standard',
                    'oOrder'               => $oOrder,
                    'msg'                  => $msg,
                    'postAuthor'           => $postAuthor,
                    'customerPhoneNumber'  => $customerPhoneNumber,
                    'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                    'aProducts'            => $aData['standard']['aProducts'][$postAuthor],
                    'isSent'               => $sentMsg,
                    'isFocus'              => $isFocus
                  ]
                );
            }
        }

        if (isset($aData['booking']) && !empty($aData['booking']['aProducts'])) {
            $msg = \WilokeThemeOptions::getOptionDetail($bookingKey);
            foreach ($aData['authors'] as $postAuthor) {
                if (!isset($aData['booking']['aProducts'][$postAuthor])) {
                    continue;
                }

                $shopOwnerPhoneNumber = User::getPhone($postAuthor);
                $msg                  = $this->cleanMessage(
                  [
                    'oOrder'               => $oOrder,
                    'orderID'              => $oOrder->get_id(),
                    'customerID'           => $oOrder->get_customer_id(),
                    'msg'                  => $msg,
                    'customerPhoneNumber'  => $customerPhoneNumber,
                    'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                    'postAuthor'           => $postAuthor,
                    'aProducts'            => $aData['booking']['aProducts'][$postAuthor]
                  ]
                );

                if ($isSendingSMSToBooking) {
                    if (!empty($msg)) {
                        try {
                            $oService = SMSFactory::getService([
                              'userID'      => $oOrder->get_customer_id(),
                              'msg'         => $msg,
                              'phoneNumber' => $customerPhoneNumber,
                              'country'     => $customerCountry
                            ]);
                            $oService->send();
                            $sentMsg = true;
                            $oOrder->update_meta_data($storeSentKey, current_time('timestamp', true));
                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'Sent a SMS message to Customer %s %s on %s at %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $oOrder->get_customer_id()),
                                $oService->getSendTo(),
                                date_i18n('Y-m-d', current_time('timestamp')),
                                date_i18n('h-i A', current_time('timestamp'))
                              )
                            );
                        } catch (\Exception $oException) {
                            $oOrder->add_order_note(
                              sprintf(
                                esc_html__(
                                  'We could not send a message to Customer %s %s. Reason %s',
                                  'wilcity-advanced-woocommerce'
                                ),
                                User::getField('display_name', $oOrder->get_user_id()),
                                $customerPhoneNumber,
                                $oException->getMessage()
                              )
                            );

                            $sentMsg = false;
                        }
                    }
                }

                do_action(
                  'wilcity/wilcity-advanced-woocommerce/app/controller/customer-sms-message-controller/sent-message',
                  [
                    'type'                 => 'booking',
                    'oOrder'               => $oOrder,
                    'msg'                  => $msg,
                    'postAuthor'           => $postAuthor,
                    'customerPhoneNumber'  => $customerPhoneNumber,
                    'shopOwnerPhoneNumber' => $shopOwnerPhoneNumber,
                    'aProducts'            => $aData['booking']['aProducts'][$postAuthor],
                    'isSent'               => $sentMsg,
                    'isFocus'              => $isFocus
                  ]
                );
            }
        }
    }

    public function sendSMS($order, $isFocus = false, $to = 'both'): bool
    {
    	if (!defined('WILCITY_SC_VERSION')) {
    		return false;
	    }

        $oOrder = $order instanceof \WC_Order ? $order : new \WC_Order($order);

        if ($to == 'shop_owner' || $to == 'both') {
            $this->sendSMSToShopOwner($oOrder, $isFocus);
        }

        if ($to == 'customer' || $to == 'both') {
            $this->sendSMSToCustomer($oOrder, $isFocus);
        }

        return true;

    }

    public function addThemeOptionSettings($aOptions)
    {
        $config             = include WILCITY_ADVANCED_WOOCOMMERCE_SMS_DIR.'configs/themeoptions.php';
        $aOptions['fields'] = array_merge($aOptions['fields'], $config);

        return $aOptions;
    }
}
