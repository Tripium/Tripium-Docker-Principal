<?php

namespace WilokeInstagramFeedhub\Controllers;

use WilokeInstagramFeedhub\Helpers\Message;
use WilokeInstagramFeedhub\Helpers\Option;

/**
 * Class RemoteDataController
 * @package WilokeInstaFeedHub\Controllers
 */
class RemoteDataController
{
	/**
	 * @var string
	 */
	public static $optionKey = 'wil_insta_shopify';

	/**
	 * RemoteDataController constructor.
	 */
	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouter']);
		add_filter('body_class', [$this, 'addClassesToBody']);
	}

	public function registerRouter()
	{
		register_rest_route(WILOKE_IFH_NAMESPACE, '/remote-data',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'updateData'],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteData'],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	/**
	 * @param \WP_REST_Request $oRequest
	 *
	 * @return array|\WP_REST_Response
	 */
	public function updateData(\WP_REST_Request $oRequest)
	{
		if (defined('IFH_URL')) {
			return Message::success(['msg' => 'InstafeedHub WP will handle it']);
		}

		if ($this->verifyAccessToken() == false) {
			return Message::error(__('The access token is invalid', 'wiloke-instafeedhub-wp'), 400);
		}

		$aParams = $oRequest->get_params();
		if (empty($aParams)) {
			return Message::error(__('There is no data', 'wiloke-instafeedhub'), 400);
		}

		$postID = floatval($aParams['id']);
		$aData = Option::get(self::$optionKey);
		if (empty($aData) || !is_array($aData)) {
			Option::update(self::$optionKey, [$postID => $aParams]);
		} else {
			if (isset($aData[$postID])) {
				if ($aParams['status'] !== 'publish') {
					unset($aData[$postID]);
				} else {
					foreach ($aParams as $pKey => $pVal) {
						if (is_numeric($pKey)) {
							$aParams[$pKey] = floatval($pVal);
						}
					}
					$aData[$postID] = $aParams;
				}

				Option::update(self::$optionKey, $aData);
			} else {
				if ($aParams['status'] !== 'publish') {
					return Message::error(__('This post status is not publish', 'wiloke-instafeedhub'), 400);
				}
				$aData[$postID] = $aParams;
				Option::update(self::$optionKey, $aData);
			}
		}

		return Message::success(['msg' => 'This post has been update successfully']);
	}

	/**
	 * @param \WP_REST_Request $oRequest
	 *
	 * @return array|\WP_REST_Response
	 */
	public function deleteData(\WP_REST_Request $oRequest)
	{
		if ($this->verifyAccessToken() == false) {
			return Message::error(__('The access token is invalid', 'wiloke-instafeedhub'), 400);
		}

		$postID = $oRequest->get_param('id');

		if (empty($postID)) {
			return Message::error(__('The post id is required', 'wiloke-instafeedhub'), 400);
		}
		$aData = Option::get(self::$optionKey);

		if (empty($aData)) {
			return Message::error(__('This post has been deleted or it does not exist', 'wiloke-instafeedhub'),
				400);
		} else {
			if (is_array($aData) && isset($aData[$postID])) {
				unset($aData[$postID]);
				Option::update(self::$optionKey, $aData);
			} else {
				return Message::error(__('This post has been deleted or it does not exist', 'wiloke-instafeedhub'),
					400);
			}
		}

		return Message::success('This post has been deleted successfully');
	}

	public function addClassesToBody($aBody)
	{
		$aBody[] = 'template-index';

		return $aBody;
	}

	public function verifyAccessToken()
	{
		return trim($_SERVER['HTTP_DOMAIN'], '/') === trim(WILOKE_INSTAFEEDHUB_URL, '/');
	}
}
