<?php

namespace WilcityAdvancedProducts\Controllers;

use WC_Customer;
use WilcityAdvancedProducts\Helpers\Cookie;
use WilokeListingTools\Controllers\Retrieve\AjaxRetrieve;
use WilokeListingTools\Controllers\RetrieveController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Validation;

class CheckoutController extends Controller
{
    private $aCustomerInfo = [];
    private $aProductIds = [];
    private $aOrderInfo = [];
    private $aSelectedFields = [];
    private $oCustomer;
    
    public function __construct()
    {
        //        add_action('wilcity/footer/vue-popup-wrapper', [$this, 'dynamicCheckoutPopup']);
        add_action('wp_ajax_nopriv_wilcity_directly_order', [$this, 'handleDirectlyOrder']);
        add_action('wp_ajax_wilcity_directly_order', [$this, 'handleDirectlyOrder']);
        add_action('wp_enqueue_scripts', [$this, 'printSettings'], 99);
    }
    
    private function cleanData(array $aData)
    {
        $aValues = [];
        $aFields = wilcityAdvancedWooCommerceGetFile()->setFile('checkout-popup')->getAll();
        
        foreach ($aData as $key => $val) {
            if (!isset($aFields[$key])) {
                continue;
            }
            
            $key    = sanitize_text_field($key);
            $cbFunc = isset($aFields[$key]['sanitizeCb']) ? $aFields[$key]['sanitizeCb'] : 'sanitize_text_field';
            
            $aValues[$key] = Validation::deepValidation($val, $cbFunc);
        }
        
        return $aValues;
    }
    
    private function getCustomerInfo($info)
    {
        return isset($this->aCustomerInfo[$info]) ? $this->aCustomerInfo[$info] : '';
    }
    
    private function create()
    {
        return [
          'payment_method'       => 'bacs',
          'payment_method_title' => 'BASC',
          'set_paid'             => false,
          'billing'              => ''
        ];
    }
    
    private function createCustomerAddressInfo()
    {
        $aSelectedFields = $this->getSelectedFields();
        foreach ($aSelectedFields as $fieldKey => $aFieldInfo) {
            $aData[$fieldKey] = $this->getCustomerInfo($fieldKey);
        }
        $aData['country'] = get_option('woocommerce_default_country');
        
        return apply_filters(
          'wilcity/filter/wilcity-advanced-woocommerce/app/Controllers/createCustomerAddressInfo/billing-address',
          $aData
        );
    }
    
    public function handleDirectlyOrder()
    {
        global $wpdb;
        
        $oRetrieve = new RetrieveController(new AjaxRetrieve());
        
        if (!isset($_POST['products']) || empty($_POST['products']) || !Validation::isValidJson($_POST['products'])) {
            $oRetrieve->error(
              [
                'msg' => esc_html__('Please select 1 product at least', 'wilcity-advanced-woocommerce')
              ]
            );
        }
        
        $this->aProductIds = Validation::getJsonDecoded();
        foreach ($this->aProductIds as $aProduct) {
            if (get_post_type($aProduct['ID']) !== 'product' || get_post_status($aProduct['ID']) !== 'publish') {
                $oRetrieve->error(
                  [
                    'msg' => esc_html__('Invalid product id', 'wilcity-advanced-woocommerce')
                  ]
                );
            }
        }
        
        if (!is_user_logged_in()) {
            if (!isset($_POST['customerInfo']) || empty($_POST['customerInfo']) ||
                !Validation::isValidJson($_POST['customerInfo'], false)) {
                $oRetrieve->error(
                  [
                    'msg' => esc_html__('Please fill up all required information', 'wilcity-advanced-woocommerce')
                  ]
                );
            }
            
            $oCustomerInfo = Validation::getJsonDecoded();
            Cookie::set('customer_info', get_object_vars($oCustomerInfo));
            
            foreach ($this->getSelectedFields() as $fieldKey => $aField) {
                if (!isset($aField['isRequired']) || !$aField['isRequired']) {
                    continue;
                }
                
                if (!isset($oCustomerInfo->{$fieldKey}) || empty($oCustomerInfo->{$fieldKey}) ||
                    (isset($aField['validateCb']) && !$aField['validateCb']($oCustomerInfo->{$fieldKey}))) {
                    if (isset($aField['msg'])) {
                        $msg = $aField['msg'];
                    } else {
                        $msg = sprintf(
                          esc_html__('The %s is required', 'wilcity-advanced-woocommerce'),
                          $aField['label']
                        );
                    }
                    
                    $oRetrieve->error(
                      [
                        'msg' => $msg
                      ]
                    );
                }
            }
            
            $this->aCustomerInfo = $this->cleanData(get_object_vars((object)$oCustomerInfo));
        } else {
            $this->aOrderInfo['customer_id'] = get_current_user_id();
        }
        
        $this->aOrderInfo = apply_filters(
          'wilcity/filter/wilcity-advanced-woocommerce/app/Controllers/CheckoutController/handleDirectlyOrder/order-info',
          $this->aOrderInfo
        );
        
        $order = wc_create_order($this->aOrderInfo);
        
        // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
        try {
            foreach ($this->aProductIds as $aProduct) {
                $order->add_product(wc_get_product($aProduct['ID']), 1);
                if (isset($aProduct['cartKey'])) {
                    WC()->cart->remove_cart_item($aProduct['cartKey']);
                }
            }
            
            if (!is_user_logged_in()) {
                $order->set_address($this->createCustomerAddressInfo(), 'billing');
            }
            
            $order->calculate_totals();
            $order->update_status('on-hold', 'Custom Booked', true);
            
            if ($customerNote = $this->getCustomerInfo('customer_note')) {
                $order->add_order_note($wpdb->_real_escape($customerNote));
            }
            
            do_action(
              'wilcity/wilcity-advanced-woocommerce/app/Controllers/CheckoutController/handleDirectlyOrder/after/order-created',
              $order,
              $this->aOrderInfo
            );
            
            $oRetrieve->success(
              apply_filters(
                'wilcity-advanced-woocommerce/filter/success-msg',
                [
                  'heading' => esc_html__('Order confirmed!', 'wilcity-advanced-woocommerce'),
                  'msg'     => esc_html__(
                    'Your order is currently being processed. You will receive an order confirmation email shortly',
                    'wilcity-advanced-woocommerce'
                  )
                ],
                $this->aCustomerInfo
              )
            );
        } catch (\WC_Data_Exception $e) {
            apply_filters(
              'wilcity-advanced-woocommerce/filter/error-msg',
              [
                'heading' => esc_html__('Order failed!', 'wilcity-advanced-woocommerce'),
                'msg'     => $e->getMessage()
              ]
            );
        }
    }
    
    private function getCustomerStoredInfo($info, $default = '')
    {
        if (!is_user_logged_in()) {
            $aCookie = Cookie::get('customer_info');
            
            return isset($aCookie[$info]) ? $aCookie[$info] : $default;
        }
        
        $value = '';
        
        if (empty($this->oCustomer)) {
            try {
                $this->oCustomer = new WC_Customer(get_current_user_id());
                $method          = 'get_'.$info;
                
                if (!empty($this->oCustomer) && !is_wp_error($this->oCustomer)) {
                    if (method_exists($this->oCustomer, $method)) {
                        $value = $this->oCustomer->$method();
                    } else {
                        switch ($info) {
                            case 'address_1':
                                $value = $this->oCustomer->get_billing_address_1();
                                break;
                            default:
                                $value = get_user_meta(get_current_user_id(), $info, true);
                                break;
                        }
                    }
                }
            } catch (\Exception $e) {
                return $default;
            }
        }
        
        return apply_filters(
          'wilcity/filter/wilcity-advanced-woocommerce/app/CheckoutController/customer-stored-info',
          $value,
          $info
        );
    }
    
    /**
     * Get default information that stored in the website and put it to popup
     *
     * @param bool $isGetDefault
     *
     * @return array
     */
    protected function getSelectedFields($isGetDefault = false)
    {
        if (!empty($this->aSelectedFields)) {
            return $this->aSelectedFields;
        }
        
        $aSelectedPopupFields = \WilokeThemeOptions::getOptionDetail('advanced_woo_checkout_fields');
        $aFields              = wilcityAdvancedWooCommerceGetFile()->setFile('checkout-popup')->getAll();
        
        if (!is_array($aSelectedPopupFields)) {
            return $aFields;
        }
        
        unset($aSelectedPopupFields['enabled']['placebo']);
        
        if (empty($aSelectedPopupFields['enabled'])) {
            return $aFields;
        }
        
        $aSelectedFields = [];
        
        foreach ($aSelectedPopupFields['enabled'] as $fieldKey => $fieldName) {
            if (isset($aFields[$fieldKey])) {
                $aSelectedFields[$fieldKey]               = $aFields[$fieldKey];
                $aSelectedFields[$fieldKey]['isRequired'] = isset($aFields[$fieldKey]['isRequired']);
                
                if ($isGetDefault) {
                    $aSelectedFields[$fieldKey]['value'] = $this->getCustomerStoredInfo($fieldKey);
                }
            }
        }
        
        $this->aSelectedFields = $aSelectedFields;
        
        return $aSelectedFields;
    }
    
    public function printSettings()
    {
        //        if (is_user_logged_in()) {
        //            return false;
        //        }
        
        $checkoutType = \WilokeThemeOptions::getOptionDetail('advanced_woo_checkout_type', 'redirect');
        $aPopupFields = [];
        if ($checkoutType !== 'redirect') {
            $aPopupFields = $this->getSelectedFields(true);
        }

        wp_localize_script('wilcity-empty', 'WIL_ADVANCED_WOOCOMMERCE', [
          'checkoutPopupFields' => $aPopupFields
        ]);
    }
}
