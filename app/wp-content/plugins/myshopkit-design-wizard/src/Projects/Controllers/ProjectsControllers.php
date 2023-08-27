<?php

namespace MyshopKitDesignWizard\Projects\Controllers;

use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Projects\Services\Post\CreatePostService;
use MyshopKitDesignWizard\Projects\Services\Post\DeletePostService;
use MyshopKitDesignWizard\Projects\Services\Post\ProjectQueryService;
use MyshopKitDesignWizard\Projects\Services\Post\UpdatePostService;
use MyshopKitDesignWizard\Projects\Services\PostMeta\AddPostMetaService;
use MyshopKitDesignWizard\Projects\Services\PostMeta\UpdatePostMetaService;
use MyshopKitDesignWizard\Shared\Middleware\TraitMainMiddleware;
use MyshopKitDesignWizard\Shared\Post\Query\PostSkeleton;
use WP_REST_Request;
use WP_REST_Response;
use WP_Term;

class ProjectsControllers
{
	use TraitMainMiddleware;

	public array  $aFieldsMetaData        = ['thumbnail', 'metadata', 'taxonomies', 'parentID', 'content'];
	public string $aFieldsProjectResponse = 'color,endpoint,id,isGlobalTemplate,label,taxonomies,thumbnail,thumbnails,totalChildren,totalChildrenText';

	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouters']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'getProjects', [$this, 'ajaxGetProjects']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'getChildrenProjects', [$this, 'ajaxGetChildrenProjects']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'getTrashProjects', [$this, 'ajaxGetTrashProjects']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'deleteTrashProjects', [$this, 'ajaxDeleteTrashProjects']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'createProjects', [$this, 'ajaxCreateProjects']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'updateProjectToTrash', [$this, 'ajaxUpdateProjectToTrash']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'deleteProject', [$this, 'ajaxDeleteProject']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'updateProject', [$this, 'ajaxUpdateProject']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'getProjectDetail', [$this, 'ajaxGetProjectDetail']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'crateTags', [$this, 'ajaxCrateTags']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'searchProjects', [$this, 'ajaxSearchProjects']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'createChildrenProject', [$this, 'ajaxCrateChildrenProject']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'updateRestoreProject', [$this, 'ajaxUpdateRestoreProject']);
		add_action('wp_ajax_' . MYSHOPKIT_DW_PREFIX . 'updateChildrenProjectToTrash',
			[$this, 'ajaxUpdateChildrenProjectToTrash']);
	}

	public function registerRouters()
	{
		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/create',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'createMyProject'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getMyProjects'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/trash',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getMyProjectsTrash'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteAllProjectsTrash'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/(?P<id>(\d+))/trash',
			[
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'updateProjectToTrash'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/(?P<parentID>(\d+))/children',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getAllChildrenProject'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteProject'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/(?P<parentID>(\d+))/create',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'createProjectChildren'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/(?P<parentID>(\d+))/detail/publish',
			[
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'updateRestoreProject'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/(?P<parentId>(\d+))/detail',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getProjectDetail'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteTrashProject'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'updateProject'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'me/projects/(?P<id>(\d+))/detail/trash',
			[
				[
					'methods'             => 'PUT',
					'callback'            => [$this, 'updateProjectToTrash'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'search',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'searchProjects'],
					'permission_callback' => '__return_true'
				]
			]
		);

		register_rest_route(MYSHOPKIT_DW_REST, 'tags',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'createTags'],
					'permission_callback' => '__return_true'
				]
			]
		);

	}

	/**
	 * @throws Exception
	 */
	public function ajaxGetProjects()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->getMyProjects($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxDeleteTrashProjects()
	{

		$aParams = [
			'postType'       => AutoPrefix::namePrefix('my_projects'),
			'status'         => 'trash',
			'posts_per_page' => -1,
			'pluck'          => 'id',
		];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				$oRequest->set_param($key, $val);
			}
		}
		$aResponse = $this->getMyProjectsTrash($oRequest);
		$aItems = $aResponse->get_data()['items'];
		foreach ($aItems as $aPost) {

			$aPostChildrens = get_posts([
				'post_parent'   => $aPost['id'],
				'post_type'     => AutoPrefix::namePrefix('my_projects'),
				'post_per_page' => -1
			]);
			if (count($aPostChildrens) > 0) {
				foreach ($aPostChildrens as $aPostChildren) {
					wp_delete_post($aPostChildren->ID, true);
				}
			}
			wp_delete_post($aPost['id'], true);
		}

		MessageFactory::factory('ajax')->successCreatior(
			'All trash items have been emptied',
			[]
		);
	}

	public function ajaxCreateProjects()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->createMyProject($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxCrateTags()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->createTags($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxGetTrashProjects()
	{
		$aParams = $_POST['params'] ?? [];
		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {
				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->getMyProjectsTrash($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxGetChildrenProjects()
	{
		$aParams = $_POST['params']['params'] ?? [];
		$aParams['url'] = $_POST['params']['url'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('parentID', $aMarches[0]);
					continue;
				}

				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->getAllChildrenProject($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxUpdateProject()
	{
		$aParams = $_POST['params']['data'] ?? [];
		$aParams['url'] = $_POST['params']['url'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('parentId', $aMarches[0]);
					continue;
				}

				$oRequest->set_param($key, $val);
			}
		}
		$oResponse = $this->updateProject($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxUpdateRestoreProject()
	{
		$aParams['url'] = $_POST['params']['url'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('parentID', $aMarches[0]);
					continue;
				}

				$oRequest->set_param($key, $val);
			}
		}
		$oResponse = $this->updateRestoreProject($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxDeleteProject()
	{
		$aParams['url'] = $_POST['params']['url'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('parentId', $aMarches[0]);
					continue;
				}

				$oRequest->set_param($key, $val);
			}
		}
		$oResponse = $this->deleteTrashProject($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxCrateChildrenProject()
	{
		$aParams = $_POST['params']['data'] ?? [];
		$aParams['url'] = $_POST['params']['url'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('parentID', $aMarches[0]);
					continue;
				}

				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->createProjectChildren($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxUpdateProjectToTrash()
	{

		$aParams['url'] = $_POST['params']['url'] ?? '';

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

		$oResponse = $this->updateProjectToTrash($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxUpdateChildrenProjectToTrash()
	{

		$aParams['url'] = $_POST['params']['url'] ?? '';

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

		$oResponse = $this->updateProjectToTrash($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxGetProjectDetail()
	{

		$aParams['url'] = $_POST['params']['url'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				if ($key == 'url') {
					preg_match('/\d+/', $val, $aMarches);
					$oRequest->set_param('parentId', $aMarches[0]);
					continue;
				}

				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->getProjectDetail($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}

	public function ajaxSearchProjects()
	{

		$aParams = $_POST['params'] ?? '';

		$oRequest = new WP_REST_Request();
		if (!empty($aParams)) {
			foreach ($aParams as $key => $val) {

				$oRequest->set_param($key, $val);
			}
		}

		$oResponse = $this->searchProjects($oRequest);

		MessageFactory::factory('ajax')->successCreatior(
			$oResponse->get_data()['msg'],
			$oResponse->get_data()
		);
	}


	public function getMyProjectsTrash(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$paged = $oRequest->get_param('paged') ?? 1;
			$pluck = $oRequest->get_param('pluck') ?: $this->aFieldsProjectResponse;
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
				],
				[
					'userID' => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aResponse = (new ProjectQueryService())
				->setRawArgs(
					[
						'postType'       => AutoPrefix::namePrefix('my_projects'),
						'status'         => 'trash',
						'posts_per_page' => $oRequest->get_param('posts_per_page'),
						'paged'          => $paged
					]
				)
				->parseArgs()
				->query(new PostSkeleton(), $pluck);


			if ($aResponse['status'] == 'error') {
				throw new Exception(esc_html__('Sorry, We could not find your project',
					'myshopkit-design-wizard'), 401);
			}
			$maxPages = $aResponse['data']['maxPages'];
			$items = $aResponse['data']['items'];
			$maxPosts = count($items);
			return MessageFactory::factory('rest')->successCreatior($aResponse['message'],
				[
					'maxPages' => $maxPages,
					'maxPosts' => $maxPosts,
					'items'    => $items,
					'paged'    => $paged
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function getProjectDetail(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$postID = $oRequest->get_param('parentId');
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
				],
				[
					'userID' => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aResponse = (new ProjectQueryService())
				->setRawArgs(
					[
						'postType' => AutoPrefix::namePrefix('my_projects'),
						'id'       => $postID
					]
				)
				->parseArgs()
				->query(new PostSkeleton(),
					'color,content,endpoint,id,isGlobalTemplate,label,taxonomies,thumbnail,thumbnails');


			if ($aResponse['status'] == 'error') {
				throw new Exception(esc_html__('Sorry, We could not find your project',
					'myshopkit-design-wizard'), 401);
			}
			$item = $aResponse['data']['items'][0] ?? '';
			return MessageFactory::factory('rest')->successCreatior($aResponse['message'],
				[
					'item' => $item
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function createMyProject(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn'
				],
				[
					'userID' => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}
			$aPostResponse = (new CreatePostService())
				->setRawData($oRequest->get_params())
				->performSaveData();

			if ($aPostResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}

			return MessageFactory::factory('rest')->successCreatior($aPostResponse['message'],
				[
					'id' => (int)$aPostResponse['data']['id']
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function updateProject(WP_REST_Request $oRawRequest): WP_REST_Response
	{
		try {
			$oRequest = $this->handleFormatMetaData($oRawRequest);
			$postID = $oRequest->get_param('parentId');
			$parentID = (int)$oRequest->get_param('parent');
			if (!empty($parentID)) {
				$oRequest->set_param('parentID', $parentID);
			}

			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware'
				],
				[
					'userID' => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization'),
					'postID' => $postID
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aPostResponse = (new UpdatePostService())
				->setID($postID)
				->setRawData($oRequest->get_params())
				->performSaveData();

			if ($aPostResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}
			$aPostMetaResponse = (new UpdatePostMetaService())
				->setID($aPostResponse['data']['id'])
				->updatePostMeta($oRequest->get_params());

			if ($aPostMetaResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}

			return MessageFactory::factory('rest')->successCreatior($aPostResponse['message'],
				[
					'id' => $aPostResponse['data']['id']
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function deleteAllProjectsTrash(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {

			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
				],
				[
					'userID' => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aParams = [
				'postType'       => AutoPrefix::namePrefix('my_projects'),
				'status'         => 'trash',
				'posts_per_page' => -1,
				'pluck'          => 'id',
			];
			$oRequest = new WP_REST_Request();
			if (!empty($aParams)) {
				foreach ($aParams as $key => $val) {
					$oRequest->set_param($key, $val);
				}
			}
			$aResponse = $this->getMyProjectsTrash($oRequest);
			$aItems = $aResponse->get_data()['items'];
			foreach ($aItems as $aPost) {

				$aPostChildrens = get_posts([
					'post_parent'   => $aPost['id'],
					'post_type'     => AutoPrefix::namePrefix('my_projects'),
					'post_per_page' => -1
				]);
				if (count($aPostChildrens) > 0) {
					foreach ($aPostChildrens as $aPostChildren) {
						wp_delete_post($aPostChildren->ID, true);
					}
				}
				wp_delete_post($aPost['id'], true);
			}


			return MessageFactory::factory('rest')->successCreatior(esc_html__('All trash items have been emptied', ''),
				[]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function updateProjectToTrash(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$postID = $oRequest->get_param('id');
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware',
					'IsPostTypeExistMiddleware'
				],
				[
					'userID'   => get_current_user_id(),
					'postID'   => $postID,
					'postType' => AutoPrefix::namePrefix('my_projects'),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}
			$aPostResponse = (new UpdatePostService())
				->setID($postID)
				->setRawData([
					'status' => 'trash'
				])
				->performSaveData();

			if ($aPostResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}

			return MessageFactory::factory('rest')->successCreatior($aPostResponse['message'],
				[
					'id' => $aPostResponse['data']['id']
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function deleteTrashProject(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$postID = (int)$oRequest->get_param('parentId');
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware',
					'IsPostTypeExistMiddleware'
				],
				[
					'userID'   => get_current_user_id(),
					'postID'   => $postID,
					'postType' => AutoPrefix::namePrefix('my_projects'),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}
			$aPostChildren = get_posts([
				'post_parent'   => $postID,
				'post_type'     => AutoPrefix::namePrefix('my_projects'),
				'post_per_page' => -1
			]);
			if (count($aPostChildren) > 0) {
				foreach ($aPostChildren as $aPost) {
					wp_delete_post($aPost->ID, true);
				}
			}
			$aPostResponse = (new DeletePostService())
				->setID($postID)
				->delete();

			if ($aPostResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}

			return MessageFactory::factory('rest')->successCreatior($aPostResponse['message'],
				[
					'id' => (int)$aPostResponse['data']['id']
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function updateRestoreProject(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$postID = $oRequest->get_param('parentID');
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware',
					'IsPostTypeExistMiddleware'
				],
				[
					'userID'   => get_current_user_id(),
					'postID'   => $postID,
					'postType' => AutoPrefix::namePrefix('my_projects'),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}
			$aPostResponse = (new UpdatePostService())
				->setID($postID)
				->setRawData([
					'status' => 'active'
				])
				->performSaveData();

			if ($aPostResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}
			return MessageFactory::factory('rest')->successCreatior($aPostResponse['message'],
				[
					'id' => $aPostResponse['data']['id']
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function getAllChildrenProject(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$paged = $oRequest->get_param('paged') ?? 1;
			$parentID = $oRequest->get_param('parentID');
			$pluck = $this->aFieldsProjectResponse;
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware'
				],
				[
					'userID' => get_current_user_id(),
					'postID' => $parentID,
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aResponse = (new ProjectQueryService())
				->setRawArgs(
					array_merge(
						$oRequest->get_params(),
						[
							'postType' => AutoPrefix::namePrefix('my_projects')
						])
				)
				->parseArgs()
				->query(new PostSkeleton(), $pluck);


			if ($aResponse['status'] == 'error') {
				throw new Exception(esc_html__('Sorry, We could not find your product',
					'myshopkit-design-wizard'), 401);
			}

			$maxPages = $aResponse['data']['maxPages'];
			$items = $aResponse['data']['items'];
			$maxPosts = count($items);

			return MessageFactory::factory('rest')->successCreatior($aResponse['message'],
				[
					'maxPages' => $maxPages,
					'maxPosts' => $maxPosts,
					'items'    => $items,
					'paged'    => $paged
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function getMyProjects(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$paged = $oRequest->get_param('paged') ?? 1;
			$pluck = $oRequest->get_param('pluck') ?: $this->aFieldsProjectResponse;
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
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aResponse = (new ProjectQueryService())
				->setRawArgs(
					array_merge($oRequest->get_params(), [
						'postType' => AutoPrefix::namePrefix('my_projects')
					])
				)
				->parseArgs()
				->query(new PostSkeleton(), $pluck);


			if ($aResponse['status'] == 'error') {
				throw new Exception(esc_html__('Sorry, We could not find your project',
					'myshopkit-design-wizard'), 401);
			}
			$maxPages = $aResponse['data']['maxPages'];
			$items = $aResponse['data']['items'];
			$maxPosts = count($items);
			return MessageFactory::factory('rest')->successCreatior($aResponse['message'],
				[
					'maxPages' => $maxPages,
					'maxPosts' => $maxPosts,
					'items'    => $items,
					'paged'    => $paged
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function searchProjects(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$paged = $oRequest->get_param('paged') ?? 1;
			$pluck = $oRequest->get_param('pluck') ?: $this->aFieldsProjectResponse;
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
				],
				[
					'userID' => get_current_user_id(),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aResponse = (new ProjectQueryService())
				->setRawArgs(
					array_merge($oRequest->get_params(), [
						'postType' => AutoPrefix::namePrefix('my_projects')
					])
				)
				->parseArgs()
				->query(new PostSkeleton(), $pluck);


			if ($aResponse['status'] == 'error') {
				throw new Exception(esc_html__('Sorry, We could not find your project',
					'myshopkit-design-wizard'), 401);
			}
			$maxPages = $aResponse['data']['maxPages'];
			$items = $aResponse['data']['items'];
			$maxPosts = count($items);
			return MessageFactory::factory('rest')->successCreatior($aResponse['message'],
				[
					'maxPages' => $maxPages,
					'maxPosts' => $maxPosts,
					'items'    => $items,
					'paged'    => $paged
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function createTags(WP_REST_Request $oRequest): WP_REST_Response
	{
		try {
			$aTags = array_map(function ($name) {
				return trim($name);
			}, explode(',', $oRequest->get_param('items')));
			$aResponseTag = [];
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
				],
				[
					'IsUserLoggedIn',
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			if (!empty($aTags)) {
				foreach ($aTags as $name) {
					if (empty($aTag = term_exists($name, AutoPrefix::namePrefix('post_tag')))) {
						$aTag = wp_insert_term($name, AutoPrefix::namePrefix('post_tag'));
					}
					$aResponseTag[] = [
						'id'    => (int)$aTag['term_id'],
						'label' => $name
					];
				}
			}

			return MessageFactory::factory('rest')->successCreatior('The tags have been inserted successfully',
				[
					'items' => $aResponseTag
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}

	public function handleFormatMetaData(WP_REST_Request $oRequest): WP_REST_Request
	{
		$aRawParams = $oRequest->get_params();

		foreach ($this->aFieldsMetaData as $key) {
			if ($key == 'parentID') {
				if (isset($aRawParams[$key])) {
					$parentID = (int)$aRawParams[$key];
					$oRequest->set_param($key, $parentID);
				}
				continue;
			}
			if ($key == 'content' && !empty($aRawParams[$key])) {
				$oRequest->set_param('content', base64_encode($aRawParams[$key]));
				continue;
			}
			if (isset($aRawParams[$key]) && !empty($aRawParams[$key])) {
				$oRequest->set_param($key, json_encode($aRawParams[$key]));
			}
		}

		return $oRequest;
	}

	public function createProjectChildren(WP_REST_Request $oRawRequest): WP_REST_Response
	{
		try {
			$oRequest = $this->handleFormatMetaData($oRawRequest);
			$aResponseMiddleware = $this->processMiddleware(
				[
					'IsUserLoggedIn',
					'IsPostExistMiddleware'
				],
				[
					'userID' => get_current_user_id(),
					'postID' => $oRequest->get_param('parentID'),
					'authorization' => $oRequest->get_header('Authorization')
				]
			);

			if ($aResponseMiddleware['status'] == 'error') {
				throw new Exception($aResponseMiddleware['message'], 401);
			}

			$aPostResponse = (new CreatePostService())
				->setRawData($oRequest->get_params())
				->performSaveData();

			if ($aPostResponse['status'] == 'error') {
				throw new Exception($aPostResponse['message'], $aPostResponse['code']);
			}
			$aResponse = (new AddPostMetaService())
				->setID($aPostResponse['data']['id'])
				->addPostMeta($oRequest->get_params());

			if ($aResponse['status'] == 'error') {
				throw new Exception($aResponse['message'], $aResponse['code']);
			}
			return MessageFactory::factory('rest')->successCreatior($aPostResponse['message'],
				[
					'id' => (int)$aPostResponse['data']['id']
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->errorCreatior($exception->getMessage(), $exception->getCode());
		}
	}
}