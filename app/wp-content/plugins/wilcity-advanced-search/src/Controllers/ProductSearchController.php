<?php


namespace WilcityAdvancedSearch\Controllers;

use WC_Tax;
use WilokeListingTools\AlterTable\AlterTableUserLatLng;
use WilokeListingTools\Framework\Helpers\App;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Validation;
use WilokeListingTools\Framework\Routing\Controller;

class ProductSearchController extends Controller
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

//		add_filter(
//			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/createQuery',
//			[$this, 'createQuery'],
//			10,
//			3
//		);

//		add_filter(
//			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/fetchListings/product/query',
//			[$this, 'handleQuery'],
//			10,
//			6
//		);

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

		// Vendor near by mex
		add_filter('posts_join', [$this, 'joinUserLatLngTbl'], 10, 2);
		add_filter('posts_fields', [$this, 'addWhereShopNearByMe'], 10, 2);
		add_filter('posts_where', [$this, 'addWhereMapBoundsToQuery'], 10, 2);
		add_filter('posts_clauses', [$this, 'addFilterByPrice'], 10, 2);
		add_filter('posts_pre_query', [$this, 'addHavingDistance'], 10, 2);
		add_filter('posts_orderby', [$this, 'orderByDistance'], 10, 2);
		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/QueryHelper/buildTaxQuery/taxonomies',
			[$this, 'addProductCategoriesToBuildTaxQuery'],
			10,
			2
		);
//		add_filter('users_pre_query', [$this, 'testQuery'], 10, 2);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/Controllers/SearchFormController/printSearchFormQuery',
			[$this, 'addExcludeProductFromMapIfDokanDisabled']
		);
	}

	public function addExcludeProductFromMapIfDokanDisabled($aExcludes)
	{
		if (!class_exists('\WeDevs_Dokan')) {
			$aExcludes[] = 'product';
		}

		return $aExcludes;
	}

	public function addProductCategoriesToBuildTaxQuery($aTaxonomies, $aRequest)
	{
		if ($aRequest['postType'] === 'product') {
			$aTaxonomies[] = 'product_cat';
			$aTaxonomies[] = 'product_tag';
		}

		return $aTaxonomies;
	}

	private function hasQueryVar($that, $key)
	{
		if (!isset($that->query_vars[$key]) || empty($that->query_vars[$key]) || $that->query_vars[$key] === 'no') {
			return false;
		}

		return true;
	}

	protected function isProductSearch($aArgs)
	{
		return isset($aArgs['post_type']) && $aArgs['post_type'] == 'product';
	}

	protected function isShopOwnersSearch($aArgs)
	{
		return isset($aArgs['geocode']) && !empty($aArgs['geocode']);
	}

	private function parseQueryVars($key, $that)
	{
		if (is_string($that->query_vars[$key])) {
			if (Validation::isValidJson($that->query_vars[$key])) {
				$that->query_vars[$key] = Validation::getJsonDecoded();
			}
		}

		return true;
	}

	protected function isNearByMeQuery($aArgs)
	{
		if ($this->isAdminQuery() ||
			!$this->isProductSearch($aArgs) ||
			!$this->isShopOwnersSearch($aArgs)
		) {
			return false;
		}

		return true;
	}

	/**
	 * @param $join
	 * @param $that
	 * @return string
	 */
	public function joinUserLatLngTbl($join, $that)
	{
		if (!$this->isNearByMeQuery($that->query_vars)) {
			return $join;
		}

		global $wpdb;
		$latLngTbl = $wpdb->prefix . AlterTableUserLatLng::$tblName;
		if (strpos($join, $latLngTbl) === false) {
			$joinLatLng = " LEFT JOIN $latLngTbl ON ($wpdb->posts.post_author = $latLngTbl.userId)";
			if (strpos($join, $latLngTbl) === false) {
				$join .= $joinLatLng;
			}
		}
		return $join;
	}

	public function orderByDistance($orderBy, $that)
	{
		if (!$this->isNearByMeQuery($that->query_vars)) {
			return $orderBy;
		}

		return 'wiloke_distance';
	}

	public function addHavingDistance($nothing, $that)
	{
		if (!$this->isNearByMeQuery($that->query_vars)) {
			return $nothing;
		}

		global $wpdb;
		$radius = $wpdb->_real_escape($that->query_vars['geocode']['radius']);
		$that->request = str_replace('ORDER BY', 'HAVING wiloke_distance < ' . $radius . ' ORDER BY', $that->request);

		return $nothing;
	}

	/**
	 * Join wc_product_meta_lookup to posts if not already joined.
	 *
	 * @param string $sql SQL join.
	 * @return string
	 */
	private function appendProductSortingTableJoin($sql)
	{
		global $wpdb;

		if (!strstr($sql, 'wc_product_meta_lookup')) {
			$sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
		}

		return $sql;
	}

	public function addFilterByPrice($aArgs, $that)
	{
		global $wpdb;
		if (!isset($that->query_vars['isWilcitySearch']) ||
			$that->query_vars['post_type'] !== 'product' ||
			(!isset($that->query_vars['price_range']) || empty($that->query_vars['price_range']))) {
			return $aArgs;
		}

		$rawMaxPrice = $wpdb->_real_escape($that->query_vars['price_range']['max']);
		$rawMaxPrice = str_replace(['&lt;', '&gt;'], ['<', '>'], $rawMaxPrice);

		if (strpos($rawMaxPrice, '>') !== false) {
			$compare = strpos($rawMaxPrice, '=') !== false ? '>=' : '>';
			$minPrice = floatval(str_replace($compare, '', $rawMaxPrice));
			$maxPrice = PHP_INT_MAX;
		} elseif (strpos($rawMaxPrice, '<') !== false) {
			$compare = strpos($rawMaxPrice, '=') !== false ? '<=' : '<';
			$maxPrice = floatval(str_replace($compare, '', $rawMaxPrice));
			$minPrice = 0;
		} else {
			$maxPrice = floatval($rawMaxPrice);
			$minPrice = floatval($that->query_vars['price_range']['min']);
		}

		/**
		 * Adjust if the store taxes are not displayed how they are stored.
		 * Kicks in when prices excluding tax are displayed including tax.
		 */
		if (wc_tax_enabled() && 'incl' === get_option('woocommerce_tax_display_shop') && !wc_prices_include_tax()) {
			$taxClass = apply_filters('woocommerce_price_filter_widget_tax_class', ''); // Uses standard tax class.
			$taxRates = WC_Tax::get_rates($taxClass);

			if ($taxRates) {
				$minPrice -= WC_Tax::get_tax_total(WC_Tax::calc_inclusive_tax($minPrice, $taxRates));
				$maxPrice -= WC_Tax::get_tax_total(WC_Tax::calc_inclusive_tax($maxPrice, $taxRates));
			}
		}

		$aArgs['join'] = $this->appendProductSortingTableJoin($aArgs['join']);
		$aArgs['where'] .= $wpdb->prepare(
			' AND wc_product_meta_lookup.min_price >= %f AND wc_product_meta_lookup.max_price <= %f ',
			$minPrice,
			$maxPrice
		);
		return $aArgs;
	}

	public function addWhereMapBoundsToQuery($where, $that)
	{
		if (!$this->isNearByMeQuery($that->query_vars) || !$this->hasQueryVar($that, 'map_bounds')) {
			return $where;
		}

		$this->parseQueryVars('map_bounds', $that);

		global $wpdb;
		$latLngTbl = $wpdb->prefix . AlterTableUserLatLng::$tblName;
		$additional
			= " AND ( ($latLngTbl.lat >= " . $wpdb->_real_escape($that->query_vars['map_bounds']['aFLatLng']['lat']) .
			" AND $latLngTbl.lat <= " . $wpdb->_real_escape($that->query_vars['map_bounds']['aSLatLng']['lat']) .
			") AND ( $latLngTbl.lng >= " . $wpdb->_real_escape($that->query_vars['map_bounds']['aFLatLng']['lng']) .
			" AND  $latLngTbl.lng <= " . $wpdb->_real_escape($that->query_vars['map_bounds']['aSLatLng']['lng']) .
			" ) )";
		$where .= $additional;


		return $where;
	}

	public function addWhereShopNearByMe($field, $that)
	{
		if (!$this->isNearByMeQuery($that->query_vars)) {
			return $field;
		}

		if (strpos($field, 'as wiloke_distance') !== false) {
			return $field;
		}

		global $wpdb;
		$unit = $wpdb->_real_escape($that->query_vars['geocode']['unit']);
		$aParseLatLng = explode(',', $that->query_vars['geocode']['latLng']);
		$unit = strtolower($unit) == 'km' ? 6371 : 3959;
		$lat = $wpdb->_real_escape(trim($aParseLatLng[0]));
		$lng = $wpdb->_real_escape(trim($aParseLatLng[1]));
		$latLngTbl = $wpdb->prefix . AlterTableUserLatLng::$tblName;

		$field .= ",( $unit * acos( cos( radians('" . $lat .
			"') ) * cos( radians( $latLngTbl.lat ) ) * cos( radians( $latLngTbl.lng ) - radians('" .
			$lng .
			"') ) + sin( radians('" . $lat .
			"') ) * sin( radians( $latLngTbl.lat ) ) ) ) as wiloke_distance";

		return $field;
	}

	public function testQuery($nothing, $that)
	{
		if ($this->isNearByMeQuery($that->query_vars)) {
			echo "SELECT $that->query_fields $that->query_from $that->query_where $that->query_orderby $that->query_limit";
			die;
		}
	}

	public function modifyPluck($aPluck, $aArgs)
	{
		if ($this->isProductSearch($aArgs)) {
			$aPluck = [
				'ID',
				'postID',
				'title',
				'headerCard',
				'footerCard',
				'bodyCard',
				'featuredImage',
				'ratingStar',
				'permalink',
				'oAddress',
				'logo',
				'mapInfo'
			];
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

			$args = [
				'is_vendor_query' => 'yes',
				'number'          => $aArgs['posts_per_page'],
				'offset'          => isset($aArgs['paged']) ? abs($aArgs['paged']) - 1 : 0
			];

			if (isset($aArgs['geocode'])) {
				$args['geocode'] = $aArgs['geocode'];
				$args['orderby'] = 'wiloke_distance';
			}

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
			$oPostSkeleton = App::get('ProductSkeleton');
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
			$status = GetSettings::getOptions(General::getSearchFieldToggleKey('product'), true, true, 'disable');
			if ($status == 'enable') {
				$aSettings['global']['postTypes'][] = [
					'name'          => esc_html__('Shop', 'wilcity-advanced-search'),
					'singular_name' => esc_html__('Shop', 'wilcity-advanced-search'),
					'icon'          => 'la la-list',
					'bgColor'       => '#f06292',
					'bgImg'         =>
						[
							'id'  => '13263',
							'url' => 'http://wilcity.wiloke.com/wp-content/uploads/2020/01/listing.jpg',
						],
					'desc'          => '',
					'endpoint'      => 'product',
					'postType'      => 'product',
					'menu_name'     => esc_html__('Shop Settings', 'wilcity-advanced-search'),
					'menu_slug'     => 'shop_settings',
					'group'         => 'product'
				];
			}
		}


		return $aSettings;
	}
}
