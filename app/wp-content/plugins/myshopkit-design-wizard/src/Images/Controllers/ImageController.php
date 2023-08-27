<?php

namespace MyshopKitDesignWizard\Images\Controllers;

use Exception;

use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Illuminate\Upload\Base64Upload;
use MyshopKitDesignWizard\Illuminate\Upload\ImageURLUpload;
use MyshopKitDesignWizard\Illuminate\Upload\TraitHandleThumbnail;
use MyshopKitDesignWizard\Illuminate\Upload\WPUpload;
use MyshopKitDesignWizard\Shared\Middleware\TraitMainMiddleware;
use MyshopKitDesignWizard\Shared\Post\Query\PostSkeleton;
use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

class ImageController
{
	use TraitHandleThumbnail, TraitMainMiddleware;

	private array $aParameters
		= [
			'post_mime_type',
			'posts_per_page',
			'orderby',
			'order',
			'id',
			'paged',
			'userID'
		];

	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouters']);
		add_action('after_setup_theme', [$this, 'addImageSizes']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'createImages', [$this, 'ajaxCreateImages']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'getImages', [$this, 'ajaxGetImages']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'deleteImage', [$this, 'ajaxDeleteImage']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'downloadImage', [$this, 'ajaxDownloadImage']);
	}

	public function addImageSizes()
	{
		add_image_size('5x5', 5, 5);
		add_image_size('thumbnail', get_option('thumbnail_size_w'), get_option('thumbnail_size_h'), false);
		add_image_size('medium', get_option('medium_size_w'), get_option('medium_size_h'), false);
		add_image_size('large', get_option('large_size_w'), get_option('large_size_h'), false);
	}

	public function registerRouters()
	{
		register_rest_route(MYSHOPKIT_DW_REST, 'me/images',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'uploadImage'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getImages'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/images/(?P<id>(\d+))',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getImage'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'images/(?P<id>(\d+))',
			[
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteImage'],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	public function ajaxDownloadImage()
	{
		$url = '';
		$aParams = $_POST['params']['params'] ?? [];
		if (isset($_POST['params']['url'])) {
			preg_match('/\d+/', $_POST['params']['url'], $aMarches);
			$aParams['thumbnailId'] = (int)$aMarches[0];
		}
		$aAttachment = wp_get_attachment_image_src($aParams['thumbnailId'], $aParams['size']);
		if (!empty($aAttachment)) {
			$url = $aAttachment[0];
		}
		if (empty($url)) {
			MessageFactory::factory('ajax')
				->error(
					esc_html__('The image does not exists', 'promooland'),
					422
				);
		}


		MessageFactory::factory('ajax')->successCreatior(
			'The image has been generated',
			[
				'item'   => [
					'url' => $url
				],
				'status' => 'success'
			]
		);
	}

	public function getThumbnailIDWithPostID($postID): int
	{
		$jPostMeta = get_post_meta($postID, AutoPrefix::namePrefix('project_thumbnail_id'), true);
		return !empty($jPostMeta) ? json_decode($jPostMeta, true)['id'] : 0;
	}

	public function ajaxCreateImages()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				if ($key == 'content') {
					$oRequest->set_param('content', str_replace('data:image/png;base64,', '', $val));
					continue;
				}
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->uploadImage($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxGetImages()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->getImages($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxDeleteImage()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('id', $aMarches[0]);
					continue;
				}
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->deleteImage($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function uploadImage(WP_REST_Request $oRequest)
	{
		return $this->updateImage($oRequest);
	}

	public function updateImage(WP_REST_Request $oRequest)
	{
		try {
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
				],
				[
					'userID'        => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				return MessageFactory::factory('rest')->error($aResponseMiddleware['message'], 401);
			}

			$jBody = $oRequest->get_body();
			$aBody = json_decode($jBody, true);
			$source = $oRequest->get_param('source') ?: $aBody['source'] ?? '';
			$id = $oRequest->get_param('id') ?: $aBody['id'] ?? '';
			$mediaID = (int)$oRequest->get_param('mediaID') ?: $aBody['mediaID'] ?? '';
			if (!empty($mediaID) && $this->checkImageOfDesignWizard($mediaID)) {
				$id = $mediaID;
			}
			switch ($source) {
				case 'base64':
					$oUpload = new Base64Upload();
					$aFileInfo = $aBody;
					$isPostMessage = true;
					$isSingular = true;
					break;
				case 'stock':
				case 'self_hosted': // duplicate image
					$oUpload = new ImageURLUpload();
					$aFileInfo = $aBody;
					$isSingular = true;
					break;
				default:
					$oUpload = new WPUpload();
					$aFileInfo = $oRequest->get_file_params();
					$isSingular = isset($aFileInfo['tmp']);
					break;
			}

			unset($aFileInfo['id']);
			unset($aFileInfo['source']);

			if (empty($aFileInfo)) {
				return MessageFactory::factory('rest')
					->errorCreatior(
						esc_html__('The file is required', 'myshopkit-design-wizard'),
						422
					);
			}

			$oUpload->isSingleUpload($isSingular)
				->setFile($aFileInfo);

			if ($type = $oRequest->get_param('type')) {
				$oUpload->setType($type);
			}

			if (!empty($source)) {
				$oUpload->setImageSource($source);
			}

			if ($id) {
				$oUpload->setUpdateAttachmentId($id);
			}

			$aResponse = $oUpload->processUpload();

			if ($aResponse['status'] == 'error') {
				return MessageFactory::factory('rest')->errorCreatior(
					$aResponse['message'], $aResponse['code']
				);
			}


			if ($isSingular) {
				$aItem = $aResponse['data']['item'];
				$postId = $aResponse['data']['item']['id'];
				$aItem['thumbnails'] = $this->getThumbnailDefault($postId);
				update_post_meta($postId, AutoPrefix::namePrefix('type'), 'photos');
				if (isset($isPostMessage)) {
					return MessageFactory::factory('rest')->successCreatior($aResponse['message'], [
						'items' => [$aItem]
					]);
				} else {
					return MessageFactory::factory('rest')->successCreatior($aResponse['message'], [
						'item' => $aItem
					]);
				}
			}
			$aDataImage = [];
			if (!empty($aItemIds = $aResponse['data']['items'])) {
				foreach ($aItemIds as $aItem) {
					$postId = $aItem['id'];
					$aItem['thumbnails'] = $this->getThumbnailDefault($postId);
					$aDataImage[] = $aItem;
					update_post_meta($postId, AutoPrefix::namePrefix('type'), 'photos');
				}
			}

			return MessageFactory::factory('rest')->successCreatior($aResponse['message'], [
				'items' => $aDataImage
			]);
		}
		catch (Exception $oException) {
			return MessageFactory::factory('rest')->errorCreatior(
				$oException->getMessage(), $oException->getCode()
			);
		}
	}

	public function getImages(WP_REST_Request $oRequest)
	{
		$aResponseMiddleware = $this->processMiddleware(
			[
				'IsUserLoggedIn',
			],
			[
				'userID'        => get_current_user_id(),
				'authorization' => $oRequest->get_header('Authorization')
			]
		);

		if ($aResponseMiddleware['status'] == 'error') {
			return MessageFactory::factory('rest')->error($aResponseMiddleware['message'], 401);
		}
		$aArgs = $this->handleParamArgs($oRequest->get_params());
		$aResponse = $this->queryImages($aArgs);

		return MessageFactory::factory('rest')->successCreatior(esc_html__('There is featured image',
			'myshopkit-design-wizard'),
			[
				'items'    => $aResponse['aImages'],
				'maxPages' => $aResponse['maxPages'],
				'maxPosts' => $aResponse['maxPosts'],
				'paged'    => $aResponse['paged']
			]
		);
	}

	public function handleParamArgs($aParams): array
	{
		$aArgs = [];
		foreach ($this->aParameters as $keyArgs) {
			if (array_key_exists($keyArgs, $aParams)) {
				switch ($keyArgs) {
					case 'id':
						$aArgs['p'] = $aParams['id'];
						break;
					case 'userID':
						$aArgs['author__in'] = $aParams['userID'];
						break;
					default:
						$aArgs[$keyArgs] = $aParams[$keyArgs];
				}
			}
		}

		return $aArgs;
	}

	public function queryImages($aArgs): array
	{
		$aImages = [];
		$maxPages = 1;
		$maxPosts = 1;
		$aArgs = wp_parse_args($aArgs, [
			'post_type'      => 'attachment',
			'post_mime_type' => 'image/jpeg,image/jpg,image/png',
			'post_status'    => 'inherit',
			'posts_per_page' => 20,
			'orderby'        => 'id',
			'order'          => 'desc',
			'paged'          => 1,
		]);
		if (!empty($userID = get_current_user_id())) {
			$aArgs['author__in'] = $userID;
		}

		$oQuery = new WP_Query($aArgs);
		if ($oQuery->have_posts()) {
			while ($oQuery->have_posts()) {
				$oQuery->the_post();
				$aImages[] = $this->getImageInfo($oQuery->post);
			}
			$maxPages = $oQuery->max_num_pages;
			$maxPosts = $oQuery->found_posts;
		}
		wp_reset_postdata();

		return [
			'aImages'  => $aImages,
			'maxPages' => $maxPages,
			'maxPosts' => $maxPosts,
			'paged'    => (int)$aArgs['paged']
		];
	}

	public function getImageInfo(WP_Post $oPost): array
	{
		$aThumbnails = $this->getThumbnailDefault($oPost->ID);
		return [
			'id'         => $oPost->ID,
			'label'      => $oPost->post_title,
			'url'        => wp_get_attachment_url($oPost->ID),
			'thumbnails' => $aThumbnails,
			'mediaID'    => $this->checkImageOfDesignWizard($oPost->ID) ? $oPost->ID : 0
		];
	}

	public function deleteImage(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$postID = (int)$oRequest->get_param('id');
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware',
				],
				[
					'userID'        => get_current_user_id(),
					'postID'        => $postID,
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}
			$oPost = wp_delete_post($postID, true);
			if ($oPost instanceof WP_Post) {
				return MessageFactory::factory('rest')->successCreatior('The image has been deleted successfully', []);
			}
			throw new Exception('The image has been not deleted error', 400);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	/**
	 * @throws Exception
	 */
	public function getImage(WP_REST_Request $oRequest)
	{
		if (empty(is_user_logged_in())) {
			return MessageFactory::factory('rest')
				->error(esc_html__('You must be logged in before performing this function',
					'myshopkit-design-wizard'), 401);
		}
		$postID = (int)$oRequest->get_param('id');
		if (!wp_attachment_is('image', $postID)) {
			return MessageFactory::factory('rest')->error(esc_html__('Sorry,this image does not exists in database',
				'myshopkit-design-wizard'), 401);
		}

		return MessageFactory::factory('rest')->success(esc_html__('This image fetched successfully',
			'myshopkit-design-wizard'),
			[
				'item' => $this->getImageInfo(get_post($postID))
			]
		);
	}

	public function checkImageOfDesignWizard(int $mediaID): bool
	{
		return get_post_meta($mediaID, AutoPrefix::namePrefix('type'), true) == 'photos';
	}
}
