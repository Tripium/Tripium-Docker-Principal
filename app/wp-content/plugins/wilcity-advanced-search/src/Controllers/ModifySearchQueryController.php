<?php

namespace WilcityAdvancedSearch\Controllers;

class ModifySearchQueryController
{
	private $aSearchBy         = null;
	private $aCacheListingTags = [];

	public function __construct()
	{
		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/SearchFormController/search-target',
			[$this, 'modifySearchTarget'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/SearchFormController/search-target-limit/geocoder',
			[$this, 'modifyNumberOfGeocoder'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/SearchFormController/search-target-limit/listing',
			[$this, 'modifyNumberOfListings'],
			10,
			2
		);

		add_filter(
			'wilcity/filter/wiloke-listing-tools/app/SearchFormController/search-target-limit/taxonomy',
			[$this, 'modifyNumberOfTaxonomies'],
			10,
			2
		);

		add_action('updated_option', [$this, 'flushSearchCache']);

		add_filter('wilcity/filter/wiloke-listing-tools/wp_search', [$this, 'modifyWPSearchQuery'], 10, 2);
		add_filter('query_vars', [$this, 'customQuery']);
		add_filter('posts_join', [$this, 'maybeJoinTermRelationships'], 10, 2);
		add_filter('wiloke-listing-tools/search-form-controller/query-args', [$this, 'addAdvancedSearchVar'], 10, 2);

//        add_filter('wilcity/wiloke-listing-tools/filter/multi-tax-logic', [$this, 'modifyTermsLogicOnSearch'], 10, 2);

//		add_filter('wiloke-listing-tools/search-form-controller/query-args', [$this, 'modifySearchArgs']);
	}

	/**
	 * @param $aArgs
	 */
	public function modifySearchArgs($aArgs)
	{
		if (isset($aArgs['post_type']) && $aArgs['post_type'] == 'product') {
//			var_export($aArgs);die;
		}

		return $aArgs;
	}

	public function modifyTermsLogicOnSearch($logic, $taxonomy)
	{
		$modifiedLogic = \WilokeThemeOptions::getOptionDetail('search_' . $taxonomy . '_terms_logic');
		if (!empty($modifiedLogic)) {
			return $modifiedLogic;
		}

		return $logic;
	}

	/**
	 * @return array|bool|null
	 */
	protected function getDefaultSearchBy()
	{
		if ($this->aSearchBy !== null) {
			return $this->aSearchBy;
		}

		$aRawSearchBy = \WilokeThemeOptions::getOptionDetail('default_search_search_by');
		if (empty($aRawSearchBy) || empty($aRawSearchBy['enabled'])) {
			$this->aSearchBy = [];

			return false;
		}

		$this->aSearchBy = (array)$aRawSearchBy['enabled'];
		unset($this->aSearchBy['placebo']);

		return $this->aSearchBy;
	}

	private function isAdvancedSearch($that)
	{
		return isset($that->query_vars['advanced_search']) && $that->query_vars['advanced_search'] === 'yes';
	}

	/**
	 * @param $search
	 *
	 * @return array|bool
	 */
	private function getListingTags($search, $postType = '')
	{
		if (empty($postType)) {
			$cacheKey = $search;
		} else {
			$postType = is_array($postType) ? md5(serialize($postType)) : $postType;
			$cacheKey = $search . $postType;
		}

		if (isset($this->aCacheListingTags[$cacheKey])) {
			return $this->aCacheListingTags[$cacheKey];
		}

		$aResults = get_terms(
			[
				'taxonomy'  => 'listing_tag',
				'fields'    => 'id=>name',
				'name'      => $search,
				'postTypes' => $postType
			]
		);

		if (empty($aResults)) {
			$this->aCacheListingTags[$cacheKey] = false;

			return false;
		}

		$aTerms = [];
		foreach ($aResults as $termId => $name) {
			$aTerms[] = abs($termId);
		}

		$this->aCacheListingTags[$cacheKey] = $aTerms;

		return $aTerms;
	}

	/**
	 * @param $aVars
	 *
	 * @return array
	 */
	public function customQuery($aVars)
	{
		$aVars[] = 'advanced_search';

		return $aVars;
	}

	/**
	 * @param $aArgs
	 * @param $aRequest
	 *
	 * @return mixed
	 */
	public function addAdvancedSearchVar($aArgs, $aRequest)
	{
		if (isset($aRequest['searchTarget']) && in_array('listing', $aRequest['searchTarget'])) {
			$aArgs['advanced_search'] = 'yes';
		}

		return $aArgs;
	}

	public function maybeJoinTermRelationships($join, $that)
	{
		if (!$this->isAdvancedSearch($that)) {
			return $join;
		}

		global $wpdb;

		if (isset($this->aSearchBy['listing_tags'])) {
			$join .= " LEFT JOIN $wpdb->term_relationships as wilcity_tr_advanced_search ON (wilcity_tr_advanced_search.object_id = $wpdb->posts.ID)";
		}

		return $join;
	}

	/**
	 * @param $keyword
	 *
	 * @return bool|string
	 */
	public function modifyWPSearchQuery($keyword, $that)
	{
		if (!$this->isAdvancedSearch($that)) {
			return $keyword;
		}

		$aSearchBy = $this->getDefaultSearchBy();
		if (empty($aSearchBy)) {
			return false;
		}

		global $wpdb;
		$aSpecialSearch = [];

		$likeKeyword = '%' . $keyword . '%';
		if (array_key_exists('post_title', $aSearchBy)) {
			$aSpecialSearch[] = $wpdb->prepare(
				"($wpdb->posts.post_title LIKE %s)",
				$likeKeyword
			);
			unset($aSearchBy['post_title']);
		}

		if (array_key_exists('post_content', $aSearchBy)) {
			$aSpecialSearch[] = $wpdb->prepare(
				"($wpdb->posts.post_content LIKE %s)",
				$likeKeyword
			);
			unset($aSearchBy['post_content']);
		}

		if (isset($aSearchBy['listing_tags'])) {
			$aListingTags = $this->getListingTags($keyword, $that->query_vars['post_type']);
			if (!empty($aListingTags)) {
				$aSpecialSearch[] = $wpdb->prepare(
					"wilcity_tr_advanced_search.term_taxonomy_id IN (" . implode(',', $aListingTags) . ")"
				);
			}

			unset($aSearchBy['listing_tags']);
		}

		if (!empty($aSearchBy)) {
			$aSpecialSearch[] = $wpdb->prepare(
				"($wpdb->postmeta.meta_key IN ('" . implode("','", array_keys($aSearchBy)) .
				"') AND $wpdb->postmeta.meta_value LIKE %s)",
				$likeKeyword
			);
		}

		return ' AND (' . implode(' OR ', $aSpecialSearch) . ')';
	}

	private function isAllowModifying($aRequest)
	{
		return isset($aRequest['module']) && $aRequest['module'] === 'complex';
	}

	public function flushSearchCache($options)
	{
		if ($options != 'wiloke_themeoptions' && $options != 'wiloke_themeoptions-transients') {
			return false;
		}

		do_action('wilcity/wiloke-listing-tools/focus-flush-cache');
	}

	public function modifyNumberOfTaxonomies($number, $aRequest)
	{
		if (!$this->isAllowModifying($aRequest)) {
			return $number;
		}

		return \WilokeThemeOptions::getOptionDetail('number_of_taxonomies', $number);
	}

	public function modifyNumberOfListings($number, $aRequest)
	{
		if (!$this->isAllowModifying($aRequest)) {
			return $number;
		}

		return \WilokeThemeOptions::getOptionDetail('number_of_listings', $number);
	}

	public function modifyNumberOfGeocoder($number, $aRequest)
	{
		if (!$this->isAllowModifying($aRequest)) {
			return $number;
		}

		return \WilokeThemeOptions::getOptionDetail('number_of_geocoder', $number);
	}

	public function modifySearchTarget($aSearchTarget, $aRequest)
	{
		if (!$this->isAllowModifying($aRequest)) {
			return $aSearchTarget;
		}

		$aFields = \WilokeThemeOptions::getOptionDetail('complex_search_target');
		if (!is_array($aFields) || empty($aFields['enabled'])) {
			return $aSearchTarget;
		}

		return array_keys($aFields['enabled']);
	}
}
