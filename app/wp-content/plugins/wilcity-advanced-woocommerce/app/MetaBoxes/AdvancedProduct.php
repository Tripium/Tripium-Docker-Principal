<?php

namespace WilcityAdvancedProducts\MetaBoxes;

use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Routing\Controller;

class AdvancedProduct extends Controller
{
    public function __construct()
    {
        add_action('save_post', [$this, 'saveSettingsInWP52'], 10, 3);
        add_action('init', [$this, 'saveSettingsWP53'], 1);
        add_action('cmb2_admin_init', [$this, 'registerMyAdvancedProductsMetaBox'], 10);
        add_filter('cmb2_types_esc_term_ajax_search', [$this, 'modifyEscapeValue'], 10, 4);
    }

    public function modifyEscapeValue($output, $metaValue, $aArgs, $that)
    {
       if ($aArgs['id'] === 'wilcity_my_advanced_product_cats') {
           return $that->get_default();
       }

       return $output;
    }

    public function registerMyAdvancedProductsMetaBox()
    {
        if (!class_exists('WooCommerce') || !$this->isCurrentAdminListingType()) {
            return false;
        }

        if (!$this->isDisableMetaBlock(['fieldKey' => 'my_advanced_products'])) {
            $aMyProducts = wilcityAdvancedWooCommerceGetFile()->setFile('metaboxes')->get('myAdvancedProducts');
            new_cmb2_box($aMyProducts);
        }
    }

    private function saveAdvancedProducts($listingID, $post, $updated)
    {
        $aPostTypeKeys = General::getPostTypeKeys(true, false);

        if (!in_array($post->post_type, $aPostTypeKeys)) {
            return false;
        }

        if (isset($_POST['wilcity_my_advanced_product_mode'])) {
            SetSettings::setPostMeta(
              $listingID,
              'my_advanced_product_mode',
              $_POST['wilcity_my_advanced_product_mode']
            );
        }

        // array [1,2,3]
        if (isset($_POST['wilcity_my_advanced_products'])) {
            $aMyProducts = array_map('absint', $_POST['wilcity_my_advanced_products']);
            SetSettings::setPostMeta($listingID, 'wilcity_my_advanced_products', $aMyProducts);
        } else {
            SetSettings::deletePostMeta($listingID, 'wilcity_my_advanced_products');
        }
        if (isset($_POST['wilcity_my_advanced_product_cats'])) {
            $aMyProducts = array_map('absint', $_POST['wilcity_my_advanced_product_cats']);
            SetSettings::setPostMeta($listingID, 'wilcity_my_advanced_product_cats', $aMyProducts);
        } else {
            SetSettings::deletePostMeta($listingID, 'wilcity_my_advanced_product_cats');
        }
    }

    public function saveSettingsWP53()
    {
        if (!$this->isWP53() || !$this->isSavedPostMeta()) {
            return false;
        }

        $this->saveAdvancedProducts($_POST['post_ID'], get_post($_POST['post_ID']), true);
    }

    public function saveSettingsInWP52($listingID, $post, $updated)
    {
        if ($this->isWP53()) {
            return false;
        }

        if (!current_user_can('administrator') || !$this->isAdminEditing()) {
            return false;
        }

        $this->saveAdvancedProducts($listingID, $post, $updated);
    }

    public static function getMyProductMode()
    {
        if (!isset($_GET['post']) || empty($_GET['post'])) {
            return '';
        }

        return GetSettings::getPostMeta($_GET['post'], 'my_advanced_product_mode');
    }

    public static function getMyProducts()
    {
        if (!isset($_GET['post']) || empty($_GET['post'])) {
            return false;
        }

        $productIds = GetSettings::getPostMeta($_GET['post'], 'my_advanced_products');
        if (empty($productIds)) {
            return false;
        }

        $aParseIds = array_filter($productIds, function ($id) {
            return get_post_field('ID', $id);
        });

        return implode(',', $aParseIds);
    }

    public static function getMyProductCats()
    {
        if (!isset($_GET['post']) || empty($_GET['post'])) {
            return false;
        }

        $catIds = GetSettings::getPostMeta($_GET['post'], 'my_advanced_product_cats');

        if (empty($catIds) || !is_array($catIds)) {
            return false;
        }

        return array_filter($catIds, function ($id) {
            return term_exists($id, 'product_cat');
        });
    }
}
