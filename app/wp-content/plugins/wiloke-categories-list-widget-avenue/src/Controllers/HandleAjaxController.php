<?php

namespace WilokePostCategoriesAvenue\Controllers;

use Exception;
use WilokePostCategoriesAvenue\Share\TraitHandleCustomPost;
use WilokePostCategoriesAvenue\Share\TraitHandleCustomProduct;
use WilokePostCategoriesAvenue\Share\TraitHandleQuery;

class HandleAjaxController
{
  use TraitHandleCustomPost, TraitHandleQuery, TraitHandleCustomProduct;

  public function __construct()
  {
    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_post_category',
      [$this, 'ajaxCustomWilPostCategory']);
    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_select_post',
      [$this, 'ajaxCustomWilPostSelect']);
    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_cat_post',
      [$this, 'ajaxCustomWilCatPost']);
    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_product',
      [$this, 'ajaxCustomWilProductSelect']);
    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_product_categories',
      [$this, 'ajaxCustomWilProductCategories']);
    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_product_tags',
      [$this, 'ajaxCustomWilProductTags']);
//		add_action('wp_ajax_nopriv_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . '_custom_wil_product_categories',
//			[$this, 'ajaxCustomWilProductCategories']);
    add_action('wp_ajax_nopriv_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_custom_wil_product_tags',
      [$this, 'ajaxCustomWilProductTags']);

    add_action('wp_ajax_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_check_products_wishlist',
      [$this, 'ajaxCheckListProductsIsWishList']);
    add_action('wp_ajax_nopriv_' . WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE .
      '_check_products_wishlist',
      [$this, 'ajaxCheckListProductsIsWishList']);
  }

  public function ajaxCheckListProductsIsWishList()
  {
    try {
      $userID = sanitize_text_field($_POST['userID']);
      if (empty($userID)) {
        throw new Exception(esc_html__('Sorry, The account is not permission',
          WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE));
      }

      $productIds = sanitize_text_field($_POST['productsId']);
      if (empty($productIds)) {
        throw new Exception(esc_html__('The productsId is required!',
          WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE));
      }
      $aProductsId = array_map(function ($productId) {
        return (int)$productId;
      }, explode(",", $productIds));
      $aResponseData = [];
      set_current_user($userID);
      foreach ($aProductsId as $productId) {
        $inWishList = false;
        if (function_exists('YITH_WCWL')) {
          $inWishList = YITH_WCWL()->is_product_in_wishlist($productId);
        }
        $aResponseData[$productId] = $inWishList;
      }
      wp_send_json([
        'message' => esc_html__("found it", WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE),
        'status'  => esc_html__('success', WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE),
        'items'   => $aResponseData
      ], 200);
      die();
    }
    catch (Exception $exception) {
      wp_send_json([
        'message' => $exception->getMessage(),
        'status'  => esc_html__('error', WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE),
      ], $exception->getCode());
      die();
    }
  }


  public function ajaxCustomWilPostCategory()
  {
    $aArgs = [
      'type'       => 'post',
      'number'     => 50,
      'hide_empty' => 0,
    ];
    if (isset($_GET['q'])) {
      $aArgs['search'] = $_GET['q'];
    }
    $aCategories = get_categories($aArgs);
    if (!empty($aCategories)) {
      foreach ($aCategories as $oCategory) {
        $aResponse[] = [
          "id"   => $oCategory->term_id,
          "text" => $oCategory->name,
        ];
      }
    }
    wp_send_json($aResponse);
  }

  public function ajaxCustomWilPostSelect()
  {
    $aArgs = [
      'numberposts' => 50
    ];
    if (isset($_GET['q'])) {
      $aArgs['s'] = $_GET['q'];
    }
    $aPosts = get_posts($aArgs);
    if (!empty($aPosts)) {
      foreach ($aPosts as $aPost) {
        $aResponse[] = [
          "id"   => $aPost->ID,
          "text" => $aPost->post_title,
        ];
      }
    }
    wp_send_json($aResponse);
  }

  public function ajaxCustomWilProductSelect()
  {
    $aArgs = [
      'numberposts' => 50,
      'post_type'   => 'product'
    ];
    if (isset($_REQUEST['q']) && !empty($_REQUEST['q'])) {
      $aArgs['s'] = sanitize_text_field($_REQUEST['q']);
    }
    $aPosts = get_posts($aArgs);
    if (!empty($aPosts)) {
      foreach ($aPosts as $aPost) {
        $aResponse[] = [
          "id"   => $aPost->ID,
          "text" => $aPost->post_title,
        ];
      }
    }
    wp_send_json($aResponse);
  }

  public function ajaxCustomWilCatPost()
  {
    $aPostTypes = get_post_types([
      'capability_type' => 'post',
      'public'          => true
    ], 'objects');
    if (!empty($aPostTypes)) {
      foreach ($aPostTypes as $key => $oPostType) {
        $aResponse[] = [
          "id"   => $key,
          "text" => $oPostType->label,
        ];
      }
    }
    wp_send_json($aResponse);
  }

  public function ajaxCustomWilProductCategories()
  {
    $aArgs = [
      'taxonomy'   => 'product_cat',
      'hide_empty' => false
    ];
    if (isset($_REQUEST['q']) && !empty($_REQUEST['q'])) {
      $aArgs['name__like'] = sanitize_text_field($_REQUEST['q']);
    }

    $categoriesProduct = get_terms(
      $aArgs
    );
    $response = [];
    foreach ($categoriesProduct as $oCategory) {
      $response[] = [
        "id"   => $oCategory->term_id,
        "text" => $oCategory->name,
      ];
    }
    wp_send_json($response);
  }

  public function ajaxCustomWilProductTags()
  {
    $aResponse = [];
    $aArgs = [
      'taxonomy'   => 'product_tag',
      'hide_empty' => false
    ];
    if (isset($_REQUEST['q']) && !empty($_REQUEST['q'])) {
      $aArgs['name__like'] = sanitize_text_field($_REQUEST['q']);
    }

    $categoriesProduct = get_terms($aArgs);


    if (!empty($categoriesProduct)) {
      foreach ($categoriesProduct as $oCategory) {
        $aResponse[] = [
          "id"   => $oCategory->term_id,
          "text" => $oCategory->name,
        ];
      }
    }
    wp_send_json($aResponse);
  }

}