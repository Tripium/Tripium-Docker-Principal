<?php

namespace WILCITY_APP\Controllers;

use WilokeListingTools\Controllers\SearchFormController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Frontend\BusinessHours;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\FavoriteStatistic;
use WilokeListingTools\Models\UserModel;
use WilokeListingTools\Framework\Helpers\WPML;

class TermController
{
	use JsonSkeleton;

	public function __construct()
	{
		add_action('rest_api_init', function () {
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, '/listing-location/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getListingsInLocation'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, '/listing-category/(?P<id>[^/]+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getListingsInCat'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, '/listing_tag/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getListingTag'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', '/listing-location/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getListingsInLocation'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', '/listing-category/(?P<id>[^/]+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getListingsInCat'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', '/listing_tag/(?P<id>\d+)', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getListingTag'],
				'permission_callback' => '__return_true'
			]);

		});
	}

	private function maybeNextPage($taxonomy, $id, $page, $maxPages)
	{
		if ($maxPages <= $page) {
			return false;
		}

		return abs($page) + 1;
	}

	private function buildQuery($term, $taxonomy, $aData)
	{
		$aData[$taxonomy] = $term;
		if (!isset($aData['postType']) || empty($aData['postType'])) {
			$aData['postType'] = General::getPostTypeKeys(false, true);
		}

		if (!isset($aData['postsPerPage']) || (abs($aData['postsPerPage']) > 100)) {
			$aData['postsPerPage'] = 18;
		}

		$aArgs = SearchFormController::buildQueryArgs($aData);

		return $aArgs;
	}

	public function itemSkeleton($post)
	{
		$aSizes = apply_filters('wilcity/mobile/featured-image/sizes', [
			'large',
			'medium',
			'thumbnail',
			'wilcity_500x275',
			'wilcity_290x165',
			'wilcity_360x200'
		]);

		if (has_post_thumbnail($post->ID)) {
			foreach ($aSizes as $size) {
				if (is_array($size)) {
					$sizeName = 'wilcity_' . $size[0] . 'x' . $size[1];
				} else {
					$sizeName = $size;
				}
				$aFeaturedImg[$sizeName] = get_the_post_thumbnail_url($post->ID, $size);
			}
		}

		$aResponse = $this->listingSkeleton($post);

		return apply_filters('wilcity/mobile/list-listings', $aResponse, $post);
	}

	public function getListingsInLocation($aData)
	{
		WPML::switchLanguageApp();
		$page = 1;
		if (isset($aData['page'])) {
			$page = abs($aData['page']);
		}
		$aArgs = $this->buildQuery($aData['id'], 'listing_location', $aData);

		$query = new \WP_Query(WPML::addFilterLanguagePostArgs($aArgs));

		if (!$query->have_posts()) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('There are no listings', WILCITY_MOBILE_APP)
			];
		}

		$aResponse = [];
		while ($query->have_posts()) {
			$query->the_post();
			$aResponse[] = $this->itemSkeleton($query->post);
		}

		$aReturn = [
			'status'   => 'success',
			'maxPages' => $query->max_num_pages,
			'total'    => $query->found_posts
		];;

		if ($nextPage = $this->maybeNextPage('listing-location', $aData['id'], $page, $query->max_num_pages)) {
			$aReturn['next'] = $nextPage;
		} else {
			$aReturn['next'] = false;
		}

		$aReturn['aListings'] = $aResponse;

		return $aReturn;
	}

	public function getListingTag($aData)
	{
		WPML::switchLanguageApp();
		$page = 1;
		if (isset($aData['page'])) {
			$page = abs($aData['page']);
		}

		$aArgs = $this->buildQuery($aData['id'], 'listing_tag', $aData);
		$query = new \WP_Query(WPML::addFilterLanguagePostArgs($aArgs));

		if (!$query->have_posts()) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('There are no listings', WILCITY_MOBILE_APP)
			];
		}

		$aResponse = [];
		while ($query->have_posts()) {
			$query->the_post();
			$aResponse[] = $this->itemSkeleton($query->post);
		}

		$aReturn = [
			'status'   => 'success',
			'maxPages' => $query->max_num_pages,
			'total'    => $query->found_posts
		];;

		if ($nextPage = $this->maybeNextPage('listing-tag', $aData['id'], $page, $query->max_num_pages)) {
			$aReturn['next'] = $nextPage;
		} else {
			$aReturn['next'] = false;
		}

		$aReturn['aListings'] = $aResponse;

		return $aReturn;
	}

	public function getListingsInCat($aData)
	{
		WPML::switchLanguageApp();
		$page = 1;
		if (isset($aData['page'])) {
			$page = abs($aData['page']);
		}

		$aArgs = $this->buildQuery($aData['id'], 'listing_cat', $aData);

		$query = new \WP_Query(WPML::addFilterLanguagePostArgs($aArgs));

		if (!$query->have_posts()) {
			return [
				'status' => 'error',
				'msg'    => esc_html__('There are no listings', WILCITY_MOBILE_APP)
			];
		}

		$aResponse = [];
		while ($query->have_posts()) {
			$query->the_post();
			$aResponse[] = $this->itemSkeleton($query->post);
		}

		$aReturn = [
			'status'   => 'success',
			'maxPages' => $query->max_num_pages,
			'total'    => $query->found_posts
		];;

		if ($nextPage = $this->maybeNextPage('listing-category', $aData['id'], $page, $query->max_num_pages)) {
			$aReturn['next'] = $nextPage;
		} else {
			$aReturn['next'] = false;
		}

		$aReturn['oResults'] = $aResponse;

		return $aReturn;
	}
}
