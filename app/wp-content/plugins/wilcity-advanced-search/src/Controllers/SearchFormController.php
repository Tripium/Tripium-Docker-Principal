<?php


namespace WilcityAdvancedSearch\Controllers;


use WilokeListingTools\Framework\Helpers\App;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;

class SearchFormController
{
	protected $postType = 'product';

	public function __construct()
	{
		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/OptimizeScripts/inline-global/buildScriptCode',
			[$this, 'addProductPostTypeToSearchV2']
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/args',
			[$this, 'modifyQueryArgs']
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/PostSkeleton',
			[$this, 'replacePostSkeleton'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/createQuery',
			[$this, 'createQuery'],
			10,
			3
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/query',
			[$this, 'handleQuery'],
			10,
			6
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/config/listing-settings/orderby',
			[$this, 'addBestSalesToOrderBy'],
			10
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/pluck',
			[$this, 'modifyPluck'],
			10,
			2
		);
	}

	protected function isProductSearch($aArgs)
	{
		return isset($aArgs['post_type']) && $aArgs['post_type'] == 'product';
	}

	protected function isShopOwnersSearch($aArgs)
	{
		return isset($aArgs['geocode']) && !empty($aArgs['geocode']);
	}

	public function modifyPluck($aPluck, $aArgs)
	{
		if ($this->isProductSearch($aArgs)) {
			$aPluck = ['headerCard', 'footerCard', 'bodyCard', 'title'];
		}

		return $aPluck;
	}

	public function addBestSalesToOrderBy($aArgs)
	{
		$aArgs['best_sales'] = esc_html__('Top Sales', 'wilcity-advanced-search');
		return $aArgs;
	}

	public function handleQuery($aResponse, $query, $oPostSkeleton, $aArgs, $aPluck, $aAtts)
	{
		if ($this->isShopOwnersSearch($aArgs)) {
			foreach ($query->users as $oVendor) {
				$aResponse[] = $oPostSkeleton->getSkeleton(
					$oVendor,
					$aPluck,
					$aAtts
				);
			}
		} else {
			while ($query->have_posts()) {
				$query->the_post();
				$aPostsNotIn[] = $query->post->ID;
				$aResponse[] = $oPostSkeleton->getSkeleton(
					$query->post,
					$aPluck,
					$aAtts
				);
			}
		}

		return $aResponse;
	}

	public function createQuery($query, $aArgs, $aRequest)
	{
		if ($this->isShopOwnersSearch($aArgs)) {
			if (!function_exists('dokan_get_sellers')) {
				return false;
			}

			$args = array(
				'number' => $aArgs['posts_per_page'],
				'offset' => isset($aArgs['paged']) ? abs($aArgs['paged']) - 1 : 0
			);

			$aSellers = dokan_get_sellers($args);
			if (empty($aSellers) || is_wp_error($aSellers) || $aSellers['count'] < 1) {
				return false;
			}

			$aSellers['found_posts'] = dokan()->vendor->get_total();
			$aSellers['max_num_pages'] = absint(ceil($aSellers['found_posts'] / $aArgs['posts_per_page']));
			return (object)$aSellers;
		} else {
			$query = new \WP_Query($aArgs);
		}

		return $query;
	}

	public function replacePostSkeleton($oPostSkeleton, $aArgs)
	{
		if ($this->isProductSearch($aArgs)) {
			if ($this->isShopOwnersSearch($aArgs)) {
				$oPostSkeleton = App::get('VendorSkeleton');
			} else {
				$oPostSkeleton = App::get('ProductSkeleton');
			}
		}

		return $oPostSkeleton;
	}

	public function modifyQueryArgs($aArgs)
	{
		if (isset($aArgs['post_type']) && $aArgs['post_type'] == 'product') {
			if (isset($aArgs['meta_query'])) {
				$aArgs['meta_query'][] = [
					'key'     => 'wilcity_exclude_from_shop',
					'value'   => 'yes',
					'compare' => '!='
				];
			} else {
				$aArgs['meta_query'][] = [
					'relation' => 'OR',
					[
						'key'     => 'wilcity_exclude_from_shop',
						'compare' => 'NOT EXISTS'
					],
					[
						'key'     => 'wilcity_exclude_from_shop',
						'value'   => 'yes',
						'compare' => '!='
					]
				];
			}
		}

		return $aArgs;
	}

	public function addProductPostTypeToSearchV2($aSettings)
	{
		if (wilcityIsSearchV2()) {
			$aSettings['global']['postTypes'][] = [
				'name'          => esc_html__('Shop', 'wilcity-advanced-search'),
				'singular_name' =>esc_html__('Shop', 'wilcity-advanced-search'),
				'icon'          => 'la la-list',
				'bgColor'       => '#f06292',
				'bgImg'         =>
					array(
						'id'  => '13263',
						'url' => 'http://wilcity.wiloke.com/wp-content/uploads/2020/01/listing.jpg',
					),
				'desc'          => '',
				'endpoint'      => 'product',
				'postType'      => 'product',
				'menu_name'     => esc_html__('Shop Settings', 'wilcity-advanced-search'),
				'menu_slug'     => 'shop_settings',
				'group'         => 'product'
			];
		}

		return $aSettings;
	}
}