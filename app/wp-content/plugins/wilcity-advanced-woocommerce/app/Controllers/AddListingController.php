<?php

namespace WilcityAdvancedProducts\Controllers;

use WilokeListingTools\Framework\Helpers\App;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Select;
use WilokeListingTools\Framework\Helpers\SetSettings;

class AddListingController
{
    private $aMyProducts;
    
    public function __construct()
    {
        add_filter(
          'wilcity/filter/wiloke-listing-tools/configs/settings',
          [$this, 'addAddListingSettings'],
          10
        );
        
        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Controllers/AddListingController/section/my_advanced_products',
          [$this, 'addProductModeOptions']
        );
        
        add_filter('wilcity/filter/wiloke-listing-tools/app/AddListingController/getResults/callback/my_advanced_products',
          [$this, 'setGetResultCallBackFuncCallback']);
        
        add_filter('wilcity/filter/wiloke-listing-tools/app/AddListingController/getDefaultValue/callback/my_advanced_products',
          [$this, 'setGetDefaultResultFuncCallback']);
        
        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/DokanController/getProductsByUserID',
          [$this, 'addProductTypeToFetchDokanProduct'],
          10,
          2
        );
        
        add_filter(
          'wilcity/filter/wiloke-listing-tools/app/Validation/cleandata',
          [$this, 'setCleanMyAdvancedProductCallback'],
          10,
          2
        );
        
        add_action(
          'wiloke-listing-tools/addlisting',
          [$this, 'saveMyAdvancedProducts'],
          10,
          2
        );
    }
    
    public function setCleanMyAdvancedProductCallback($cb, $sectionKey)
    {
        if ($sectionKey === 'my_advanced_products') {
            return [
              App::get('WilcityAdvancedWooCommerceAddListingController'),
              'cleanMyAdvancedProducts'
            ];
        }
        
        return $cb;
    }
    
    public function saveMyAdvancedProducts($that, $listingID)
    {
        global $wpdb;
        if (!empty($this->aMyProducts)) {
            SetSettings::setPostMeta($listingID, 'my_advanced_product_mode',
              $wpdb->_real_escape($this->aMyProducts['my_advanced_product_mode']));
            
            if ($this->aMyProducts['my_advanced_product_mode'] === 'specify_products') {
                SetSettings::setPostMeta(
                  $listingID,
                  'my_advanced_products',
                  $this->aMyProducts['my_advanced_products']
                );
            } elseif ($this->aMyProducts['my_advanced_product_mode'] === 'specify_product_cats') {
                SetSettings::setPostMeta(
                  $listingID,
                  'my_advanced_product_cats',
                  $this->aMyProducts['my_advanced_product_cats']
                );
            }
        }
    }
    
    public function cleanMyAdvancedProducts($aResult, $sectionKey)
    {
        if (!array_key_exists(
          $aResult['my_advanced_product_mode'],
          wilokeListingToolsRepository()->get('settings:productModeOptions')
        )) {
            wp_send_json_error([
              'msg' => esc_html__('Invalid product mode', 'wilcity-advanced-woocommerce')
            ]);
        }
        
        if ($aResult['my_advanced_product_mode'] === 'specify_products') {
            $this->aMyProducts['my_advanced_products'] = Select::getSelectTreeVal($aResult['my_advanced_products']);
            if (!empty($this->aMyProducts['my_advanced_products']) &&
                is_array($this->aMyProducts['my_advanced_products'])) {
                foreach ($this->aMyProducts['my_advanced_products'] as $order => $productID) {
                    if (get_post_type($productID) !== 'product') {
                        wp_send_json_error(
                          [
                            'msg' => esc_html__(sprintf('The %d is not a product', $productID),
                              'wilcity-advanced-woocommerce')
                          ]
                        );
                    }
                }
            } else {
                $this->aMyProducts['my_advanced_products'] = [];
            }
        } elseif ($aResult['my_advanced_product_mode'] === 'specify_product_cats') {
            $this->aMyProducts['my_advanced_product_cats'] =
              Select::getSelectTreeVal($aResult['my_advanced_product_cats']);
            
            if (!empty($this->aMyProducts['my_advanced_product_cats']) &&
                is_array($this->aMyProducts['my_advanced_product_cats'])) {
                foreach ($this->aMyProducts['my_advanced_product_cats'] as $order => $catID) {
                    if (get_term_field('term_id', $catID, 'product_cat') !== $catID) {
                        wp_send_json_error(
                          [
                            'msg' => esc_html__(sprintf('The %d is not a product category', $catID),
                              'wilcity-advanced-woocommerce')
                          ]
                        );
                    }
                }
            } else {
                $this->aMyProducts['my_advanced_product_cats'] = [];
            }
        }
        
        $this->aMyProducts['my_advanced_product_mode'] = $aResult['my_advanced_product_mode'];
    }
    
    protected function removeProductTypeFromQuery($aArgs)
    {
        if (isset($aArgs['tax_query'])) {
            foreach ($aArgs['tax_query'] as $order => $aInfo) {
                if ($aInfo['taxonomy'] === 'product_type') {
                    unset($aArgs['tax_query'][$order]);
                    
                    return $aArgs;
                }
            }
        }
        
        return $aArgs;
    }
    
    public function addProductTypeToFetchDokanProduct($aArgs, $aAtts)
    {
        if (isset($aAtts['product_types'])) {
            return $aArgs;
        }
        
        $aArgs = $this->removeProductTypeFromQuery($aArgs);
        
        $aArgs['tax_query'][] = [
          'taxonomy' => 'product_type',
          'field'    => 'slug',
          'terms'    => wilcityAdvancedWooCommerceGetFile()->setFile('general')->get('product_types')
        ];
        
        return $aArgs;
    }
    
    public function addAddListingSettings($aFields)
    {
        return array_merge($aFields, wilcityAdvancedWooCommerceGetFile()->setFile('addlisting')->getAll());
    }
    
    public function setGetResultCallBackFuncCallback()
    {
        return [App::get('WilcityAdvancedWooCommerceAddListingController'), 'getMyProduct'];
    }
    
    public function setGetDefaultResultFuncCallback()
    {
        return [App::get('WilcityAdvancedWooCommerceAddListingController'), 'getDefaultMyProduct'];
    }
    
    public function getDefaultMyProduct()
    {
        return [
          'my_advanced_product_mode' => 'author_products',
          'my_advanced_products'     => [],
          'my_advanced_product_cats' => []
        ];
    }
    
    public function getMyProduct($aSection, $listingID)
    {
        $aPostIDs = GetSettings::getPostMeta($listingID, 'my_advanced_products');
        $aTermIds = GetSettings::getPostMeta($listingID, 'my_advanced_product_cats');
        
        $maximumProducts = empty($aSection['fieldGroups']['my_advanced_products']['queryArgs']['maximum']) ? 1000 :
          $aSection['fieldGroups']['my_products']['queryArgs']['maximum'];
        
        return [
          'my_advanced_product_mode' => GetSettings::getPostMeta($listingID, 'my_advanced_product_mode'),
          'my_advanced_products'     => empty($aPostIDs) ? [] : Select::buildPostsSelectTree(
            $aPostIDs,
            $aSection['fieldGroups']['my_advanced_products']['selectValueFormat'],
            $maximumProducts
          ),
          'my_advanced_product_cats' => empty($aTermIds) ? [] : Select::buildTermSelectTree(
            $aTermIds,
            'product_cat',
            $aSection['fieldGroups']['my_advanced_products']['selectValueFormat'],
            1000
          )
        ];
    }
    
    public function addProductModeOptions($aSection)
    {
        $aRawOptions = wilokeListingToolsRepository()->get('settings:productModeOptions');
        
        unset($aRawOptions['inherit']);
        $aOptions = [];
        
        foreach ($aRawOptions as $key => $val) {
            $aOptions[] = [
              'id'    => $key,
              'label' => $val
            ];
        }
        
        $aSection['fieldGroups']['my_advanced_product_mode']['options'] = apply_filters(
        	'wilcity/filter/wilcity-advanced-woocommerce/app/Controllers/AddListingController/addProductModeOptions',
	        $aOptions
        );
        
        return $aSection;
    }
}
