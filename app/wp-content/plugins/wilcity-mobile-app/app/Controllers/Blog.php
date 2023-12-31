<?php

namespace WILCITY_APP\Controllers;

use WILCITY_APP\Helpers\HsBlog;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Framework\Helpers\WPML;

class Blog
{
	use JsonSkeleton;

	public function __construct()
	{
		add_action('rest_api_init', function () {
			register_rest_route(
				WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION,
				'posts',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'fetchBlog'],
					'permission_callback' => '__return_true',
				]
			);

			register_rest_route(
				WILOKE_PREFIX . '/v2',
				'posts',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'fetchBlog'],
					'permission_callback' => '__return_true',
				]
			);

			register_rest_route(
				WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION,
				'posts/(?P<ID>\w+)',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getPost'],
					'permission_callback' => '__return_true',
				]
			);

			register_rest_route(
				WILOKE_PREFIX . '/v2',
				'posts/(?P<ID>\w+)',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getPost'],
					'permission_callback' => '__return_true',
				]
			);

			register_rest_route(
				WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION,
				'pages/(?P<ID>\w+)',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getPost'],
					'permission_callback' => '__return_true',
				]
			);

			register_rest_route(
				WILOKE_PREFIX . '/v2',
				'pages/(?P<ID>\w+)',
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getPost'],
					'permission_callback' => '__return_true',
				]
			);
		});
	}

	private function getPostTerms($postID, $type = 'category')
	{
		switch ($type) {
			case 'tag':
				$aRawTerms = wp_get_post_tags($postID);
				break;
			default:
				$aRawTerms = wp_get_post_categories($postID, $type);
				break;
		}

		if (!empty($aRawTerms) && !is_wp_error($aRawTerms)) {
			$aTerms = [];

			foreach ($aRawTerms as $termID) {
				$oTerm = get_term($termID);

				$aTerms[] = [
					'name' => $oTerm->name,
					'ID'   => $oTerm->term_id,
					'slug' => $oTerm->slug
				];
			}

			return $aTerms;
		}

		return false;
	}

	private function postListJson($post, $aData = []): array
	{
		$excerptLength = isset($aData['excerptLength']) ? $aData['excerptLength'] : 30;

		$aData = [];
		$aData['postID'] = abs($post->ID);
		$aData['postTitle'] = get_the_title($post->ID);
		$aData['postContent']
			= strip_tags(\Wiloke::contentLimit($excerptLength, $post, false, $post->post_content, true));
		$aData['oAuthor'] = [
			'avatar'      => User::getAvatar($post->post_author),
			'displayName' => User::getField('display_name', $post->post_author),
		];
		$aData['postDate'] = date_i18n(get_option('date_format'), strtotime($post->post_date));
		$commentCounts = get_comments_number($post->ID);
		$aData['countComments'] = abs($commentCounts);
		$aData['shareOn'] = \WilokeSocialNetworks::getUsedSocialNetworks();
		$aData['aCategories'] = $this->getPostTerms($post->ID);
		$aData['aTags'] = $this->getPostTerms($post->ID, 'tag');

		$aFeaturedImgs = [];
		if (has_post_thumbnail($post->ID)) {
			$aImgSizes = $this->imgSizes();
			foreach ($aImgSizes as $imgSize) {
				$aFeaturedImgs[$imgSize] = get_the_post_thumbnail_url($post->ID, $imgSize);
			}
			$aData['oFeaturedImg'] = $aFeaturedImgs;
		} else {
			$aData['oFeaturedImg'] = WILCITY_APP_IMG_PLACEHOLDER;
		}

		return $aData;
	}

	private function buildBlogQuery($aData): array
	{
		$aArgs = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'orderby'        => 'post_date',
			'paged'          => 1
		];

		if (isset($aData['postsPerPage']) && (abs($aData['postsPerPage']) <= 50)) {
			$aArgs['posts_per_page'] = $aData['postsPerPage'];
		}

		if (isset($aData['page']) && !empty($aData['page'])) {
			$aArgs['paged'] = abs($aData['page']);
		}

		if (isset($aData['category']) && !empty($aData['category'])) {
			$aArgs['cat'] = $aData['category'];
		} else if (isset($aData['categoryName']) && !empty($aData['categoryName'])) {
			$aArgs['category_name'] = trim($aData['categoryName']);
		} else if (isset($aData['categoryIn']) && !empty($aData['categoryIn'])) {
			$aParseCats = explode(',', $aData['categoryIn']);
			$aParseCats = array_map(function ($catID) {
				return abs($catID);
			}, $aParseCats);
			$aArgs['category__in'] = $aParseCats;
		}

		if (isset($aData['tag']) && !empty($aData['tag'])) {
			$aArgs['tag_id'] = abs($aData['tag']);
		} else if (isset($aData['tagSlug']) && !empty($aData['tagSlug'])) {
			$aArgs['tag'] = esc_sql($aData['tagSlug']);
		} else if (isset($aData['tagInSlug']) && !empty($aData['tagInSlug'])) {
			$aParseCats = explode(',', $aData['tagInSlug']);
			$aParseCats = array_map(function ($catID) {
				return esc_sql($catID);
			}, $aParseCats);
			$aArgs['tag_slug__in'] = $aParseCats;
		} else if (isset($aData['tagIDIn']) && !empty($aData['tagIDIn'])) {
			$aParseCats = explode(',', $aData['tagIDIn']);
			$aParseCats = array_map(function ($catID) {
				return abs($catID);
			}, $aParseCats);
			$aArgs['tag__in'] = $aParseCats;
		}

		if (isset($aData['author']) && !empty($aData['author'])) {
			$aArgs['author'] = abs($aData['author']);
		} else if (isset($aData['authorName']) && !empty($aData['authorName'])) {
			$aArgs['author_name'] = esc_sql($aData['authorName']);
		} else if (isset($aData['authorIn']) && !empty($aData['authorIn'])) {
			$aParseAuthor = explode(',', $aData['authorIn']);
			$aParseAuthor = array_map(function ($catID) {
				return esc_sql($catID);
			}, $aParseAuthor);
			$aArgs['author__in'] = $aParseAuthor;
		}

		if (isset($aData['orderBy']) && !empty($aData['orderBy'])) {
			$aArgs['orderby'] = esc_sql($aData['orderBy']);
		}

		if (isset($aData['postID']) && !empty($aData['postID'])) {
			$aArgs['post_id'] = esc_sql($aData['postID']);
		}

		if (isset($aData['getRelatedPostsBy']) && !empty($aData['getRelatedPostsBy']) && isset($aData['postID']) &&
			!empty($aData['postID'])) {
			switch ($aData['getRelatedPostsBy']) {
				case 'category':
					$aCategories = wp_get_post_categories($aData);
					if (!empty($aCategories) && !is_wp_error($aCategories)) {
						$aCategoryIDs = array_map(function ($oCategory) {
							return $oCategory->term_id;
						}, $aCategories);

						$aArgs['category__in'] = $aCategoryIDs;
					}
					break;
				case 'tag':
					$aCategories = wp_get_post_tags($aData);
					if (!empty($aCategories) && !is_wp_error($aCategories)) {
						$aCategoryIDs = array_map(function ($oCategory) {
							return $oCategory->term_id;
						}, $aCategories);

						$aArgs['tag__in'] = $aCategoryIDs;
					}
					break;
			}
		}

		return $aArgs;
	}

	public function getPost($aData): array
	{
		if (!class_exists('\WilokeThemeOptions')) {
			return $aData;
		}

		if (\WilokeThemeOptions::isEnable('toggle_hsblog')) {
			$aResponse = HsBlog::fetchPost($aData['ID']);
			if ($aResponse['status'] == 'success') {
				$aResponse['oResult']['shareOn'] = \WilokeSocialNetworks::getUsedSocialNetworks();
			}

			return $aResponse;
		} else {
			$post = get_post(abs($aData['ID']));
			if (empty($post) || is_wp_error($post)) {
				return [
					'status' => 'error',
					'msg'    => esc_html__('This page no longer available', 'wilcity-mobile-app')
				];
			}

			$aData = [];
			$aData['postID'] = abs($post->ID);
			$aData['postTitle'] = get_the_title($post->ID);
			$aData['postContent'] = do_shortcode(get_post_field('post_content', $post->ID));
			$aData['oAuthor'] = [
				'avatar'      => User::getAvatar($post->post_author),
				'displayName' => User::getField('display_name', $post->post_author),
			];
			$aData['postDate'] = date_i18n(get_option('date_format'), strtotime($post->post_date));
			$commentCounts = get_comments_number($post->ID);
			$aData['countComments'] = abs($commentCounts);
			$aData['shareOn'] = \WilokeSocialNetworks::getUsedSocialNetworks();
			$aData['aCategories'] = $this->getPostTerms($post->ID);
			$aData['aTags'] = $this->getPostTerms($post->ID, 'tag');

			$aFeaturedImgs = [];
			if (has_post_thumbnail($post->ID)) {
				$aImgSizes = $this->imgSizes();
				foreach ($aImgSizes as $imgSize) {
					$aFeaturedImgs[] = get_the_post_thumbnail_url($post->ID, $imgSize);
				}
				$aData['aFeaturedImages'] = $aFeaturedImgs;
			} else {
				$aData['aFeaturedImages'] = WILCITY_APP_IMG_PLACEHOLDER;
			}

			return [
				'status'  => 'success',
				'oResult' => $aData
			];
		}
	}

	public function fetchBlog($aData): array
	{
		WPML::switchLanguageApp();
		$aArgs = $this->buildBlogQuery($aData);
		if (\WilokeThemeOptions::isEnable('toggle_hsblog')) {
			$aResults = HsBlog::fetchPosts($aArgs);
		} else {
			$query = new \WP_Query(WPML::addFilterLanguagePostArgs($aArgs));
			$aResults = [];
			$aPosts = [];

			if ($query->have_posts()) {
				$maxPages = $query->max_num_pages;
				while ($query->have_posts()) {
					$query->the_post();
					$aPosts[] = $this->postListJson($query->post, $aData);
				}
				$aResults['status'] = 'success';
				if (get_option('show_on_front') == 'page') {
					$blogID = get_option('page_for_posts');
					$aResults['blogTitle'] = get_the_title($blogID);
				} else {
					$aResults['blogTitle'] = get_option('blogname');
				}
				$aResults['next'] = $maxPages > 1 && $maxPages > $aArgs['paged'] ? $aArgs['paged'] + 1 : false;
				$aResults['oResults'] = $aPosts;
			} else {
				$aResults['status'] = 'error';
				$aResults['msg'] = esc_html__('No Post Founds', 'wilcity-mobile-app');
			}
		}

		return $aResults;
	}

	public function getPostsFromCategory($aData): array
	{
		if (is_numeric($aData['target'])) {
			$aData['category'] = abs($aData['target']);
		} else {
			$aData['categorySlug'] = abs($aData['target']);
		}

		return $this->fetchBlog($aData);
	}

	public function getPostsFromTag($aData): array
	{
		if (is_numeric($aData['target'])) {
			$aData['tag'] = abs($aData['target']);
		} else {
			$aData['tagSlug'] = abs($aData['target']);
		}

		return $this->fetchBlog($aData);
	}

	public function getPostsByAuthor($aData): array
	{
		if (is_numeric($aData['target'])) {
			$aData['author'] = abs($aData['author']);
		} else {
			$aData['authorName'] = abs($aData['authorName']);
		}

		return $this->fetchBlog($aData);
	}
}
