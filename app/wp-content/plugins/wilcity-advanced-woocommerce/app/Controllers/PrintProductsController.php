<?php

namespace WilcityAdvancedProducts\Controllers;

use WilcityServiceClient\Helpers\PremiumPlugin;
use WilokeListingTools\Controllers\Retrieve\AjaxRetrieve;
use WilokeListingTools\Controllers\Retrieve\RestRetrieve;
use WilokeListingTools\Controllers\RetrieveController;
use WilokeListingTools\Framework\Helpers\App;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\ProductSkeleton;

class PrintProductsController
{
	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouter']);
		add_action(
			'wilcity/single-listing/home-sections/my_advanced_products',
			[$this, 'modifyTemplateDir'],
			10
		);
		add_action('wp_head', [$this, 'printSignToSingleListing']);
		add_filter(
			'wilcity/wiloke-listing-tools/WooCommerceController/filter/getProductJson',
			[$this, 'getProductLink'],
			10,
			2
		);
	}

	public function getProductLink($aInfo, \WC_Product $product)
	{
		$aInfo['link'] = $product->get_permalink();

		return $aInfo;
	}

	public function printSignToSingleListing()
	{
		?>
        <script>
            window.WILCITY_ADVANCED_WOOCOMMERCE = true;
        </script>
		<?php
	}

	public function replaceMyProductTwoKey($aSection)
	{
		if ($aSection['key'] == 'my_products2') {
			$aSection['key'] = 'my_products';
		}

		return $aSection;
	}

	public function addMyProductTwoToListingSidebar($aSidebar)
	{
		$aSidebar['renderMachine'] = array_merge($aSidebar['renderMachine'], ['renderMachine']);
	}

	public function registerRouter()
	{
		register_rest_route(
			WILOKE_PREFIX . '/v2',
			'listing-products/(?P<listingId>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'fetchListingProducts'],
					'permission_callback' => '__return_true'
				]
			]);
	}

	public function fetchListingProducts(\WP_REST_Request $oRequest)
	{
		$oRetrieve = new RetrieveController(new RestRetrieve());
		$postID = $oRequest->get_param('listingId');

		if (!PremiumPlugin::isExpired('wilcity-advanced-woocommerce')) {
			return $oRetrieve->error([
				'msg' => PremiumPlugin::getExpiryMsg('wilcity-advanced-woocommerce')
			]);
		}

		if (empty($postID)) {
			return $oRetrieve->error([
				'msg' => esc_html__('The listing id is required', 'wilcity-advanced-woocommerce')
			]);
		}

		$aProducts = GetSettings::getListingProducts($postID, 'advanced_woocommerce');

		if (empty($aProducts)) {
			return $oRetrieve->error([
				'msg' => esc_html__('Whoops! We found no products of this listing', 'wilcity-advanced-woocommerce')
			]);
		} else {
			$aResponse = [
				'currency'       => get_woocommerce_currency(),
				'currencySymbol' => get_woocommerce_currency_symbol(),
				'numDecimals'    => get_option('woocommerce_price_num_decimals'),
				'thousandSep'    => get_option('woocommerce_price_thousand_sep'),
				'currencyPos'    => get_option('woocommerce_currency_pos')
			];
			$aResponse = $aResponse + $aProducts;

			return $oRetrieve->success(
				apply_filters(
					'wilcity/wiloke-listing-tools/WooCommerceController/filter/getProducts',
					$aResponse,
					$oRequest->get_params()
				));
		}
	}

	public function modifyTemplateDir($wilcityArgs)
	{
		include WILCITY_ADVANCED_WOOCOMMERCE . 'template/advanced-product.php';
	}
}
