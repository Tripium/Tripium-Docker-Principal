<?php

namespace WooKit\Page\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WP_REST_Request;

class PageController
{
	private array $aListPostTypeRemove = ['post', 'page', 'attachment'];

	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouters']);
	}

	public function registerRouters()
	{
		register_rest_route(WOOKIT_REST, 'pages',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getPages'],
					'permission_callback' => '__return_true'
				]
			]
		);
		register_rest_route(WOOKIT_REST, 'pages/(?P<id>(\d+))',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getPage'],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	public function getPages(WP_REST_Request $oRequest)
	{
		$aItems = [];
		$limit = $oRequest->get_param('limit') ?: 200;
		if (!is_user_logged_in()) {
			return MessageFactory::factory('rest')->error(
				esc_html__('Forbidden', 'wookit'),
				403
			);
		}
		$aArgs = [
			'number' => $limit
		];
		$aPostTypes = get_post_types(['public' => true], 'objects');
		foreach ($aPostTypes as $key => $oPostType) {
			if (in_array($key, $this->aListPostTypeRemove)) {
				continue;
			}
			$aItems[] = [
				'id'         => uniqid(),
				'title'      => $oPostType->label,
				'handle'     => '(/' . $oPostType->name . ')',
				'body_html'  => '',
				'author'     => '',
				'created_at' => '',
				'updated_at' => '',
				'link'       => ''
			];
		}
		$aGetTaxonomies = get_taxonomies(['public' => true], 'objects');
		foreach ($aGetTaxonomies as $key => $oGetTaxonomies) {
			$aItems[] = [
				'id'         => uniqid(),
				'title'      => $oGetTaxonomies->label,
				'handle'     => '(/' . $oGetTaxonomies->name . ')',
				'body_html'  => '',
				'author'     => '',
				'created_at' => '',
				'updated_at' => '',
				'link'       => ''
			];
		}
		$oPages = get_pages($aArgs);
		if (empty($oPages) || is_wp_error($oPages)) {
			return MessageFactory::factory('rest')->success(
				esc_html__('We found no page', 'wookit'),
				[
					'items' => []
				]
			);
		}
		foreach ($oPages as $aPage) {
			$handle = ($aPage->ID != get_option('page_on_front')) ? $aPage->post_name : '';
			$aItems[] = [
				'id'         => (string)$aPage->ID,
				'title'      => $aPage->post_title,
				'handle'     => '/' . $handle,
				'body_html'  => $aPage->post_excerpt,
				'author'     => $aPage->post_author,
				'created_at' => $aPage->post_date,
				'updated_at' => $aPage->post_modified,
				'link'       => $aPage->guid
			];
		}
		return MessageFactory::factory('rest')->success(
			sprintf(esc_html__('We found %s items', 'wookit'), count($aItems)),
			[
				'items' => $aItems
			]
		);
	}

	public function getPage(WP_REST_Request $oRequest)
	{
		$aItem = [];
		$pageID = $oRequest->get_param('id');
		if (!is_user_logged_in()) {
			return MessageFactory::factory('rest')->error(
				esc_html__('Forbidden', 'wookit'),
				403
			);
		}
		$aArgs = [
			'include' => [$pageID]
		];
		$oPages = get_pages($aArgs);
		if (empty($oPages) || is_wp_error($oPages)) {
			return MessageFactory::factory('rest')->success(
				esc_html__('We found no page', 'wookit'),
				[
					'item' => $aItem
				]
			);
		}
		foreach ($oPages as $aPage) {
			$aItem = [
				'id'         => (string)$aPage->ID,
				'title'      => $aPage->post_title,
				'handle'     => $aPage->post_name,
				'body_html'  => $aPage->post_content,
				'author'     => $aPage->post_author,
				'created_at' => $aPage->post_date,
				'updated_at' => $aPage->post_modified,
				'link'       => $aPage->guid
			];
		}
		return MessageFactory::factory('rest')->success(
			esc_html__('We found items page', 'wookit'),
			[
				'item' => $aItem
			]
		);
	}
}
