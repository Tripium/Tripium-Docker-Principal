<?php

namespace WILCITY_APP\Controllers;

use WILCITY_APP\Helpers\App;
use WilokeListingTools\Controllers\NotificationsController as ThemeNotificationController;
use WilokeListingTools\Controllers\ReviewController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Models\NotificationsModel;
use WilokeListingTools\Models\ReviewModel;
use WilokeListingTools\Framework\Helpers\WPML;

class NotificationController
{
	use VerifyToken;
	use JsonSkeleton;
	use ParsePost;

	public function __construct()
	{
		add_action('rest_api_init', function () {
			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, 'get-my-notifications', [
				'methods'             => 'GET',
				'callback'            => [$this, 'getMyNotifications'],
				'permission_callback' => '__return_true'
			]);

			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION, '/count-new-notifications', [
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => [$this, 'countNewNotifications']
			]);

			register_rest_route(WILOKE_PREFIX . '/' . WILOKE_MOBILE_REST_VERSION,
				'/delete-my-notification/(?P<target>\w+)',
				[
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => [$this, 'deleteMyNotification']
				]);

			register_rest_route(WILOKE_PREFIX . '/v2', '/get-my-notifications', [
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => [$this, 'getMyNotifications']
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', '/count-new-notifications', [
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => [$this, 'countNewNotifications']
			]);

			register_rest_route(WILOKE_PREFIX . '/v2', '/delete-my-notification/(?P<target>\w+)', [
				'methods'             => 'DELETE',
				'permission_callback' => '__return_true',
				'callback'            => [$this, 'deleteMyNotification']
			]);
		});
	}

	public function countNewNotifications()
	{
		WPML::switchLanguageApp();
		$oToken = $this->verifyPermanentToken();
		if (!$oToken) {
			return $this->tokenExpiration();
		}
		$oToken->getUserID();

		if (isset($_GET['isGetNew']) && $_GET['isGetNew'] == 'yes') {
			$count = GetSettings::getUserMeta($this->userID, NotificationsModel::$countNewKey);
			$count = empty($count) ? 0 : abs($count);
		} else {
			$count = NotificationsModel::countAllNotificationOfUser($oToken->userID);
		}

		return [
			'status' => 'success',
			'msg'    => $count
		];
	}

	public function deleteMyNotification($aData)
	{
		WPML::switchLanguageApp();
		$oToken = $this->verifyPermanentToken();
		if (!$oToken) {
			return $this->tokenExpiration();
		}
		$oToken->getUserID();
		if (!isset($aData['target'])) {
			return [
				'status' => 'error',
				'msg'    => 'idIsRequired'
			];
		}

		$status = NotificationsModel::deleteOfReceiver($aData['target'], $oToken->userID);
		if (!$status) {
			return [
				'status' => 'error',
				'msg'    => 'couldNotDeleteNotification'
			];
		} else {
			return [
				'status' => 'success',
				'msg'    => 'deletedNotification'
			];
		}
	}

	protected function getListingReview($reviewID, $parentID, $listingType)
	{
		$aNotification['screen'] = 'CommentListingScreen';
		$aDetails = GetSettings::getOptions(General::getReviewKey('details', $listingType), false, true);
		$aNotification['oDetails'] = $this->getReviewItem(get_post($reviewID), $parentID, $aDetails);

		return $aNotification;
	}

	protected function getEventReview($ID)
	{
		$aNotification['screen'] = 'EventCommentDiscussionScreen';
		$aNotification['oDetails'] = $this->eventCommentItem(get_post($ID));

		return $aNotification;
	}

	public function getMyNotifications()
	{
		WPML::switchLanguageApp();
		$oToken = $this->verifyPermanentToken();
		if (!$oToken) {
			return $this->tokenExpiration();
		}
		$oToken->getUserID();

		$postsPerPage = isset($_GET['postsPerPage']) ? $_GET['postsPerPage'] : 10;
		$paged = isset($_GET['page']) ? $_GET['page'] : 1;
		$offset = ($paged - 1) * $postsPerPage;
		$aNotifications = NotificationsModel::get($oToken->userID, $postsPerPage, $offset);

		if (!$aNotifications) {
			return [
				'msg'    => 'noOlderNotifications',
				'status' => 'error'
			];
		} else {
			$aNotificationsInfo = [];
			$aListingTypes = GetSettings::getAllDirectoryTypes(true);

			foreach ($aNotifications['aResults'] as $oInfo) {
				$aNotification = ThemeNotificationController::getNotificationType($oInfo);
				if ($aNotification) {
					if ($aNotification['isNoLogger'] ||
						(isset($oInfo->objectID) && $this->isPostDoesNotExist($oInfo->objectID))) {
						$aNotification['isDeleted'] = true;
						$aNotification['postContent'] = wilcityAppGetLanguageFiles('contentIsNoLongerAvailable');
						if (!isset($aNotification['oFeaturedImg']) ||
							!isset($aNotification['oFeaturedImg']['thumbnail'])) {
							$aNotification['oFeaturedImg']['thumbnail'] = WILCITY_APP_URL . 'assets/img/deleted.png';
						}

						$aNotificationsInfo[] = $aNotification;
						continue;
					}

					$parentID = get_post_field('post_parent', $oInfo->objectID);
					$postType = get_post_field('post_type', $parentID);

					$aNotification['oFeaturedImg'] = [
						'thumbnail' => isset($aNotification['featuredImg']) ? $aNotification['featuredImg'] : ''
					];
					$aNotification['postTitle'] = get_the_title($parentID);
					$aNotification['postLink'] = $aNotification['link'];
					$aNotification['postContent']
						= (isset($aNotification['title']) ? strip_tags($aNotification['title']) : '') . ' ' .
						strip_tags($aNotification['content']) .
						(isset($aNotification['contentHighlight']) ?
							' ' . strip_tags($aNotification['contentHighlight']) :
							'');
					$aNotification['tagLine']
						= !in_array($postType, $aListingTypes) ? '' : GetSettings::getTagLine($parentID);

					switch ($aNotification['type']) {
						case 'review':
							$parentID = wp_get_post_parent_id($aNotification['objectID']);
							if (!empty($parentID)) {
								$listingType = get_post_type($parentID);
								$aListingReview
									= $this->getListingReview($aNotification['objectID'], $parentID, $listingType);
								$aNotification['mode'] = ReviewController::getMode($listingType);
								$aNotification = $aNotification + $aListingReview;
							}

							break;
						case 'comment_discussion':
							$parentID = wp_get_post_parent_id($aNotification['objectID']);
							if (!empty($parentID)) {
								$aEventReview = $this->getEventReview($aNotification['objectID']);
								$aNotification = $aNotification + $aEventReview;
							}
							break;
						case 'review_discussion':
							$postID = wp_get_post_parent_id($aNotification['parentID']);
							if (!empty($postID)) {
								$listingType = get_post_type($postID);
								if ($listingType == 'event') {
									$aEventReview = $this->getEventReview($aNotification['parentID']);
									$aNotification = $aEventReview + $aNotification;
								} else {
									$listingType = get_post_type($postID);
									$aListingReview
										= $this->getListingReview($aNotification['parentID'], $postID, $listingType);
									$aNotification['mode'] = ReviewController::getMode($listingType);
									$aNotification = $aNotification + $aListingReview;
								}
							}
							break;
					}

					unset($aNotification['link']);
					unset($aNotification['featuredImg']);
					unset($aNotification['content']);
					unset($aNotification['contentHighlight']);
					unset($aNotification['postLink']);
					$aNotificationsInfo[] = $aNotification;
				}
			}

			if (empty($aNotificationsInfo)) {
				if (!isset($_POST['paged'])) {
					return [
						'msg'    => 'noOlderNotifications',
						'status' => 'error'
					];
				} else {
					return [
						'msg'    => 'allNotificationsIsLoaded',
						'status' => 'error'
					];
				}
			}
			$maxPages = ceil($aNotifications['total'] / $postsPerPage);
			if ($paged < $maxPages) {
				$next = $paged + 1;
			} else {
				$next = false;
			}

			return [
				'oResults' => $aNotificationsInfo,
				'maxPages' => $maxPages,
				'status'   => 'success',
				'next'     => $next
			];
		}
	}
}
