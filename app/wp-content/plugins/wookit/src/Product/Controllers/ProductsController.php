<?php

namespace WooKit\Product\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

class ProductsController
{
	protected string $regularPriceKey = '_regular_price';
	protected string $salePriceKey    = '_sale_price';
	protected string $productImageKey = '_product_image_gallery';

	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouters']);
	}

	public function registerRouters()
	{
		register_rest_route(WOOKIT_REST, 'products',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getProducts'],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	public function getProducts(WP_REST_Request $oRequest): WP_REST_Response
	{
		$aItems = [];
		$search = $oRequest->get_param('s');
		$limit = $oRequest->get_param('limit') ?: 12;
		$page = $oRequest->get_param('page') ?: 1;
		if (!is_user_logged_in()) {
			return MessageFactory::factory('rest')->error(
				esc_html__('Forbidden', 'wookit'),
				403
			);
		}
		$aArgs = [
			'posts_per_page' => $limit,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'paged'          => $page,
		];
		if (!empty($search)) {
			$aArgs['s'] = $search;
			$aArgs['sentence'] = true;
		}
		$oQuery = new WP_Query($aArgs);
		if (!$oQuery->have_posts()) {
			wp_reset_postdata();
			return MessageFactory::factory('rest')->success(
				esc_html__('We found no product', 'wookit'),
				[
					'items' => []
				]
			);
		}

		/**
		 * @var WP_Post $aCoupon
		 */

		while ($oQuery->have_posts()) {
			$oQuery->the_post();;
			$id = $oQuery->post->ID;
			$aIdProductImage = explode(',', get_post_meta($id, $this->productImageKey, true));
			$idProductImage = $aIdProductImage[0] ?: get_post_meta($id, '_thumbnail_id', true);
			$aProductImage = wp_get_attachment_image_src($idProductImage, 'auto');

			$aImage = [
				'src'    => $aProductImage ? $aProductImage[0] : '',
				'width'  => $aProductImage ? $aProductImage[1] : '',
				'height' => $aProductImage ? $aProductImage[2] : ''
			];

			$aPrice = [
				(string)get_post_meta($id, $this->regularPriceKey, true) ?: '0',
				(string)get_post_meta($id, $this->salePriceKey, true) ?: '0',
			];
			$aItems[] = [
				'id'    => $id,
				'title' => $oQuery->post->post_title,
				'link'  => get_permalink($id),
				'image' => $aImage,
				'price' => $aPrice,
			];
		}
		$maxPages = $oQuery->max_num_pages;
		wp_reset_postdata();

		return MessageFactory::factory('rest')->success(
			sprintf(esc_html__('We found %s items', 'wookit'), count($aItems)),
			[
				'items'    => $aItems,
				'maxPages' => $maxPages
			]
		);
	}
}
