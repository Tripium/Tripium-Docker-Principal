<?php

namespace WILCITY_APP\Controllers\WooCommerce;

use Exception;
use WILCITY_APP\Controllers\WooCommerceController;
use WilokeListingTools\Framework\Helpers\App;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\WooCommerce;
use WilokeListingTools\Frontend\User;
use WP_Query;
use WP_REST_Request;
use WilokeListingTools\Framework\Helpers\WPML;

class WooCommerceProductController extends WooCommerceController
{
	public function __construct()
	{
		add_action('rest_api_init', function () {
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, 'wc/products', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getProducts'],
				'permission_callback' => '__return_true'
			]);
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, 'wc/products/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getProduct'],
				'permission_callback' => '__return_true'
			]);
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, 'wc/products/(?P<id>\d+)/variations',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getVariations'],
					'permission_callback' => '__return_true'
				]);
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION,
				'/wc/products/(?P<id>\d+)/variations/(?P<variationID>\d+)', [
					'methods'             => 'GET',
					'callback'            => [$this, 'getVariation'],
					'permission_callback' => '__return_true'
				]);
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, 'wc/products/attributes/(?P<id>\d+)',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getAttribute'],
					'permission_callback' => '__return_true'
				]);

			###
			register_rest_route(WILOKE_PREFIX . '/v2', 'wc/products', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getProducts'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', 'wc/products/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getProduct'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', 'wc/products/(?P<id>\d+)/variations', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getVariations'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', 'wc/products/(?P<id>\d+)/variations/(?P<variationID>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getVariation'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', 'wc/products/attributes/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getAttribute'],
				'permission_callback' => '__return_true'
			]);
		});

		add_filter(
			'wilcity/woocommerce/content-single-product/after-single-product',
			[$this, 'willNotShowUpIfItIsWebviewRequest']
		);

		add_filter(
			'woocommerce_is_rest_api_request',
			[$this, 'allowIncludingFrontEndWhenRequestingSingleProduct']
		);

		add_filter(
			'wilcity/wilcity-mobile-app/filter/rebuildHomePageAfterPostUpdated/allowedPostTypes',
			[$this, 'updateHomePageWhenUpdatingProduct']
		);

		add_filter(
			'wilcity/wilcity-mobile-app/filter/product-on-single-listing',
			[$this, 'hasProductsOnSingleListing'],
			10,
			3
		);

		add_filter(
			'wilcity/mobile/sidebar/my_products', [$this, 'showUpProductOnSingleListing'],
			10,
			3
		);

		add_filter(
			'wilcity/wilcity-mobile-app/filter/sidebar-settings',
			[$this, 'addTypeToProductSettings'],
			99,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/ProductSkeleton/pluck/isAddedToCart',
			[$this, 'getIsAddedToCartStatus'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/ProductSkeleton/cartKey',
			[$this, 'getAppCartKey'],
			10,
			2
		);
	}

	public function getAppCartKey($aCartKey, $productID)
	{
		$aAppCartKeys = $this->getCartKey($productID);
		if (empty($aAppCartKeys)) {
			return $aCartKey;
		}

		return $aAppCartKeys;
	}

	public function getIsAddedToCartStatus($status, $aInfo)
	{
		if (!isset($aInfo['atts']) || !isset($aInfo['atts']['isApp'])) {
			return $status;
		}

		return $this->isProductInCart($aInfo['postID']);
	}

	public function addTypeToProductSettings($aSettings, WP_REST_Request $oRequest)
	{
		if (!in_array($aSettings['key'], ['woocommerceBooking'])) {
			return $aSettings;
		}

		$aSettings['type'] = 'booking';

		return $aSettings;
	}

	public function showUpProductOnSingleListing($return, $aProductIDs, $isJsonEncode = true)
	{
		$aProducts = [];
		foreach ($aProductIDs as $productID) {
			$oProduct = wc_get_product($productID);
			if (!empty($oProduct) && !is_wp_error($oProduct) && $oProduct->get_type() != 'booking') {
				$aProducts[] = $this->getProductsJson($oProduct, get_post($productID));
			}
		}

		return $isJsonEncode ? json_encode($aProducts) : $aProducts;
	}

	public function hasProductsOnSingleListing($return, $aData, $isTestMode)
	{
		$aProductIDs = GetSettings::getMyProducts($aData['target']);

		if ($isTestMode) {
			if (!empty($aProductIDs)) {
				return true;
			}

			return false;
		}

		return $this->showUpProductOnSingleListing($return, $aProductIDs, false);
	}

	public function updateHomePageWhenUpdatingProduct($aPostTypes)
	{
		$aPostTypes[] = 'product';

		return $aPostTypes;
	}

	/**
	 * As the default, Woocommerce won't include front-end hook if it's a Rest API request. But We have to use it
	 * if it's that for WooCommerce My Room on Sidebar
	 *
	 * @see \WooCommerce:is_rest_api_request
	 * @see \WooCommerce:includes
	 */
	public function allowIncludingFrontEndWhenRequestingSingleProduct($isRequestRestAPI)
	{
		if (empty($_SERVER['REQUEST_URI'])) {
			return $isRequestRestAPI;
		}

		if ($isRequestRestAPI) {
			$isRequestRestAPI = strpos($_SERVER['REQUEST_URI'], WILOKE_PREFIX . '/v2/listing/sidebar/') === false ?
				$isRequestRestAPI : false;
		}

		return $isRequestRestAPI;
	}

	public function willNotShowUpIfItIsWebviewRequest($status)
	{
		return (isset($_REQUEST['iswebview']) && $_REQUEST['iswebview'] == 'yes') ? false : $status;
	}

	public function getProductsJson($product, $post)
	{
		return $this->productSkeleton($product, $post);
	}

	public function fixOldVersion($aProduct, $post)
	{
		$aProduct['id'] = $post->ID;
		$aProduct['name'] = $post->post_title;

		return $aProduct;
	}

	public function getProducts(WP_REST_Request $oRequest)
	{
		WPML::switchLanguageApp();
		$status = $this->auth();
		if (!$status) {
			return [
				'msg'    => $this->errAuthMsg,
				'status' => 'error'
			];
		}

		$postsPerPage = !empty($oRequest->get_param('postsPerPage')) ? $oRequest->get_param('postsPerPage') : 10;
		$paged = !empty($oRequest->get_param('page')) ? $oRequest->get_param('page') : 1;
		$order = !empty($oRequest->get_param('order')) ? $oRequest->get_param('order') : 'DESC';
		$orderBy = !empty($oRequest->get_param('orderby')) ? $oRequest->get_param('orderby') : 'date';

		$aArgs = wp_parse_args(
			[
				'post_type'   => 'product',
				'post_status' => 'publish',
				'per_page'    => $postsPerPage,
				'page'        => $paged,
				'order'       => strtolower($order),
				'orderby'     => strtolower($orderBy)
			],
			[
				'per_page' => 10,
				'page'     => 1,
				'order'    => 'desc',
				'orderby'  => 'date'
			]
		);

		$aProducts = [];
		$query = new WP_Query(WPML::addFilterLanguagePostArgs($aArgs));
		while ($query->have_posts()) {
			$query->the_post();
			$aProduct = App::get('ProductSkeleton')->getSkeleton($query->post->ID);

			$aProducts[] = $this->fixOldVersion($aProduct, $query->post);
		}
		wp_reset_postdata();

		return $this->retrieveProductsFormat($aProducts, $query->max_num_pages);
	}

	public function getProduct(WP_REST_Request $oRequest)
	{
		$oToken = $this->verifyPermanentToken();
		$userID = '';

		if ($oToken) {
			$this->getUserID();
			$userID = $oToken->userID;
		}

		$status = $this->auth();

		if (!$status) {
			return [
				'msg'    => $this->errAuthMsg,
				'status' => 'error'
			];
		}

		$pluck = $oRequest->get_param('pluck');
		$productID = $oRequest->get_param('id');

		if (get_post_status($productID) !== 'publish' || get_post_type($productID) !== 'product') {
			return [
				'status' => 'error',
				'msg'    => wilcityAppGetLanguageFiles('productDoesNotExist')
			];
		}

		switch ($pluck) {
			case 'url':
				$permalink = get_permalink($productID);

				return [
					'status' => 'success',
					'url'    => $permalink
				];
				break;
			default:
				try {
					/**
					 * @var object $oProduct
					 */
					$oProduct = $this->oWooCommerce->get('products/' . $productID);
					if (empty($oProduct)) {
						return [
							'status' => 'error',
							'data'   => wilcityAppGetLanguageFiles('productDoesNotExist')
						];
					}

					$aProduct = get_object_vars($oProduct);
					$aProduct['oFeatureImage'] = get_the_post_thumbnail_url($aProduct['id'], 'woocommerce_single');
					if (!empty($aProduct['images'])) {
						$aGalleryIDs = array_map(function ($oImg) {
							return $oImg->id;
						}, $aProduct['images']);

						$aProduct['gallery'] = $this->buildGallery($aGalleryIDs);
						unset($aProduct['images']);
					}

					unset($aProduct['_links']);
					unset($aProduct['meta_data']);
				}
				catch (Exception $oException) {
					do_action(
						'wilcity/site-error',
						esc_html__('Wilcity Warning: Could not read product on Wilcty App', 'wilcity-mobile-app'),
						sprintf(__('We could not read product data. To resolve this issue, please setup WooCommerce for your app <a href="%s">Click here to know how to do that</a>',
							'wilcity-mobile-app'),
							'https://documentation.wilcity.com/knowledgebase/setting-up-woocommerce-on-wilcity-app/'),
						'wilcity_can_not_read_product'
					);

					return [
						'status' => 'error',
						'data'   => $oException->getMessage()
					];
				}

				switch ($aProduct['type']) {
					case 'variable':
						$aRebuildAttributes = [];
						foreach ($aProduct['attributes'] as $oAttribute) {
							$aRebuildAttributes[$oAttribute->id] = $oAttribute;
						}
						$aProduct['attributes'] = $aRebuildAttributes;
						break;
					default:
						break;
				}

				$aRatings = apply_filters('wilcity/wilcity-mobile-app/filter/product-rating', [], $oRequest);

				if ($aRatings['status'] == 'success') {
					$aProduct['aRatingItems'] = $aRatings['data']['aItems'];
				}

				if (!empty($aProduct['upsell_ids'])) {
					$aProduct['relatedIDs'] = $aProduct['upsell_ids'];
				} else if ($aProduct['related_ids']) {
					$aProduct['relatedIDs'] = $aProduct['related_ids'];
				}

				unset($aProduct['upsell_ids']);
				unset($aProduct['related_ids']);
				unset($aProduct['cross_sell_ids']);

				if ($userID) {
					$aProduct['oWishlist'] = [
						'isAdded' => $this->isProductInWishlist($oRequest->get_param('id'))
					];

					if ($aProduct['oWishlist']['isAdded']) {
						$aProduct['oWishlist']['wishlistID'] = abs(YITH_WCWL()->generate_default_wishlist($userID));
						$aWishlist
							= YITH_WCWL()->get_wishlist_detail($aProduct['oWishlist']['wishlistID']);
						$aProduct['oWishlist']['wishlistToken'] = $aWishlist['wishlist_token'];
					}
				} else {
					$aProduct['oWishlist'] = [
						'isAdded' => false
					];
				}

				$postAuthor = get_post_field('post_author', $aProduct['id']);
				$aProduct['oAuthor'] = [
					'ID'          => $postAuthor,
					'displayName' => User::getField('display_name', $postAuthor),
					'avatar'      => User::getAvatar($postAuthor)
				];

				$aPrice = $this->getProductPrices(wc_get_product($aProduct['id']));
				$aProduct = array_merge($aPrice, $aProduct);
				$aProduct['aListingIDs'] = WooCommerce::getListingIDsByProductID($productID);

				return [
					'status' => 'success',
					'data'   => $aProduct
				];
				break;
		}
	}

	public function getVariations(WP_REST_Request $oRequest)
	{
		$status = $this->auth();
		if (!$status) {
			return [
				'msg'    => $this->errAuthMsg,
				'status' => 'error'
			];
		}

		$aVariationIDs = $oRequest->get_param('variations');
		$aVariationIDs = is_array($aVariationIDs) ? $aVariationIDs : explode(',', $aVariationIDs);
		if (empty($aVariationIDs)) {
			return [
				'status' => 'error',
				'msg'    => wilcityAppGetLanguageFiles('noVariationFound')
			];
		}
		$productID = $oRequest->get_param('id');
		$oProduct = wc_get_product($productID);
		$aRawAttributes = $oProduct->get_attributes();

		$aAttributes = [];
		foreach ($aRawAttributes as $attrKey => $aAttribute) {
			if ($aAttribute['data']['variation']) {
				$aAttributeNames = [];
				$aAttributeSlugs = [];
				foreach ($aAttribute['data']['options'] as $termID) {
					$oTerm = get_term($termID);
					if (!empty($oTerm) && !is_wp_error($oTerm)) {
						$aAttributeNames[$oTerm->slug] = $oTerm->name;
						$aAttributeSlugs[] = $oTerm->slug;
					}
				}

				$aAttributes[$attrKey] = [
					'id'      => $attrKey,
					'name'    => wc_attribute_label($attrKey),
					'options' => $aAttributeNames,
					'slugs'   => $aAttributeSlugs
				];
			}
		}

		$aVariations = [];
		$aUsedVariations = [];
		if (!empty($aVariationIDs)) {
			foreach ($aVariationIDs as $order => $variationID) {
				$oVariation = wc_get_product($variationID);
				$aVariations[$variationID] = $this->productSkeleton($oVariation, get_post($variationID));
				$aAttributesInVariation = $oVariation->get_attributes();
				$aAttributesInVariationKeys = array_keys($aAttributesInVariation);
				if (empty($aAttributesInVariation[$aAttributesInVariationKeys[0]])) {
					$aTerms = get_terms([
						'taxonomy'   => $aAttributesInVariationKeys[0],
						'hide_empty' => false,
						'number'     => 1,
						'exclude'    => $aUsedVariations
					]);
					if (!empty($aTerms) && !is_wp_error($aTerms)) {
						$aAttributesInVariation[$aAttributesInVariationKeys[0]] = $aTerms[0]->slug;
						$aUsedVariations[] = $aTerms[0]->term_id;
					}
				}

				$aVariations[$variationID]['oAttributes'] = $aAttributesInVariation;
			}
		}

		if (empty($aVariations)) {
			return [
				'status' => 'error',
				'data'   => wilcityAppGetLanguageFiles('productDoesNotExist')
			];
		}

		return [
			'status' => 'success',
			'data'   => [
				'oVariations' => $aVariations,
				'oAttributes' => $aAttributes
			]
		];
	}

	public function getVariation(WP_REST_Request $oRequest)
	{
		$status = $this->auth();
		if (!$status) {
			return [
				'msg'    => $this->errAuthMsg,
				'status' => 'error'
			];
		}
		$aVariation = $this->oWooCommerce->get('products/' . $oRequest->get_param('id') . '/variations/'
			. $oRequest->get_param('variationID'));

		if (empty($aVariation)) {
			return [
				'status' => 'error',
				'data'   => wilcityAppGetLanguageFiles('productDoesNotExist')
			];
		}

		return [
			'status' => 'success',
			'data'   => $aVariation
		];
	}

	public function getAttribute(WP_REST_Request $oRequest)
	{
		$this->auth();
		$aAttributes = $this->oWooCommerce->get('products/attributes/' . $oRequest->get_param('id'));

		if (empty($aAttributes)) {
			return [
				'status' => 'error',
				'data'   => wilcityAppGetLanguageFiles('productDoesNotExist')
			];
		}

		return [
			'status' => 'success',
			'data'   => $aAttributes
		];
	}
}
