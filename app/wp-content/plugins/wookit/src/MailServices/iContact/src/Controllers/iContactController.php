<?php

namespace WooKit\MailServices\iContact\src\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\MailServices\iContact\src\Shared\IcontactConnection;

class iContactController{
	use TraitMainMiddleware;

	public string $key = 'icontact_info';
	public string $mailService = 'icontact';

	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;

	public function __construct() {
		add_action(WOOKIT_HOOK_PREFIX . 'after/subscribed', [$this, 'subscribeEmailDirectly']);
		add_filter(WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
			[$this, 'configMiddleware']);
	}

	public function configMiddleware( array $aMiddleware ) {
		$aIContact = include(plugin_dir_path(__FILE__) . "../Configs/Middleware.php");

		return array_merge($aMiddleware, $aIContact);
	}

	public function getAllServiceData( $campaignID ): array {
		$aServiceData  = $this->getCurrentUserMeta(get_current_user_id());
		$aCampaignData = $this->getCurrentPostMeta($campaignID);
		$selectStatus  = $aServiceData['selected'];
		$aServiceData  = wp_parse_args($aServiceData,
			[
				'appID'       => '',
				'appUsername' => '',
				'appPassword' => '',
				'listID'      => '',
				'status'      => '',
			]
		);
		$aListData     = wp_parse_args(
			[
				'id'    => $aCampaignData['listID'] ?? '',
				'label' => $aCampaignData['listLabel'] ?? '',
			],
			[
				'id'    => '',
				'label' => '',
			]
		);

		$aApiKeys = wp_parse_args(
			[
				'appID' => !empty($aServiceData['appID']) ?
					$this->protectValue($aServiceData['appID'], 10) : null,

				'appUsername' => !empty($aServiceData['appUsername']) ?
					$this->protectValue($aServiceData['appUsername'], 10) : null,

				'appPassword' => !empty($aServiceData['appPassword']) ?
					$this->protectValue($aServiceData['appPassword'], 10) : null,
			],
			[
				'appID'       => '',
				'appUsername' => '',
				'appPassword' => '',
			]
		);

		return MessageFactory::factory()->success('OK',
			[
				'aApiKeys'      => $aApiKeys,
				'activatedList' => $aListData['id'] == '' ? null : $aListData,
				'serviceName'   => $this->mailService,
				'status'        => $aCampaignData['status'],
				'selected'      => $selectStatus,
			]
		)
			;
	}

	/**
	 * @param array $aRequest
	 *
	 * @return array a successful response will return array object with success message and status
	 *
	 * {
	 * "data": null,
	 * "message": "Hoorays! Your authentication data has been saved successfully.",
	 * "status": "success"
	 * }
	 *
	 * a failure response will return array object with error message and status plus status code
	 *
	 * {
	 * "message": "THIS-IS-ERROR-MESSAGE",
	 * "code": THIS-IS-HTTP-STATUS-CODE,
	 * "status": "error"
	 * }
	 *
	 */
	public function saveApiKey( array $aRequest ): array {
		$campaignID = $aRequest['campaignID'] ?? '';
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidIContactAPIKey',
		], [
			'appID'       => $appID = $aRequest['apiKeys']['appID'] ?? '',
			'appUsername' => $appUsername = $aRequest['apiKeys']['appUsername'] ?? '',
			'appPassword' => $appPassword = $aRequest['apiKeys']['appPassword'] ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$status    = 'active';
			$aPostMeta = $this->getCurrentPostMeta($campaignID);
			$this->updateCampaignAPIKeysConfiguration('appID', $appID);
			$this->updateCampaignAPIKeysConfiguration('appUsername', $appUsername);
			$this->updateCampaignAPIKeysConfiguration('appPassword', $appPassword);
			$this->updateCampaignAPIKeysConfiguration('selected', true);
			$this->updateCampaignListIDConfiguration($campaignID, 'status', $status);
			$aListData         = $this->getAllLists()['data'];
			$currentActiveList = empty($aPostMeta['listID'])
				? null : $aPostMeta['listID'];

			$aLists = empty($aListData['items'])
				? null : $aListData['items'];
			$aData  = [
				'name'             => $this->mailService,
				'list'             => $aLists,
				'listItemSelected' => $currentActiveList,
				'status'           => $status,
				'selected'         => true,
				'apiKeys'          => [
					'appID'       => $this->protectValue($appID, 10),
					'appUsername' => $this->protectValue($appUsername, 10),
					'appPassword' => $this->protectValue($appPassword, 10),
				],
			];

			return MessageFactory::factory()
				->success(esc_html__('Hoorays! Your Api key has been saved successfully.',
					'myshopkit'), $aData
				)
				;
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	public function getAllLists(): array {
		$aUserMeta = $this->getCurrentUserMeta(get_current_user_id());
		$aResponse = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidIContactAPIKey',
		], [
			'appID'       => $aUserMeta['appID'] ?? '',
			'appUsername' => $aUserMeta['appUsername'] ?? '',
			'appPassword' => $aUserMeta['appPassword'] ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$aLists = IcontactConnection::connect(
				$aUserMeta['appID'],
				$aUserMeta['appUsername'],
				$aUserMeta['appPassword'])
				->getLists()
			;

			return MessageFactory::factory()->success(esc_html__('This is your lists.', 'myshopkit'),
				[
					'items' => $aLists,
				]
			)
				;
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @param array $aParams
	 *
	 * @return array
	 */
	public function changeServiceStatus( array $aParams ): array {
		$campaignID = $aParams['campaignID'];
		$aResponse  = $this->saveServiceStatus($campaignID, $aParams['status'] ?? '');
		if( $aResponse['status'] == 'success' ) {
			return MessageFactory::factory()->success($aResponse['message']);
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @param array $oRequest
	 *
	 * @return array
	 *
	 *  * a successful response will return a array's object with message and status
	 *
	 * {
	 * "data": null,
	 * "message": "Hoorays! Your list ID has been saved successfully.",
	 * "status": "success"
	 * }
	 *
	 *
	 * a failure response will return array object with error message and status plus status code
	 *
	 * {
	 * "message": "THIS-IS-ERROR-MESSAGE",
	 * "code": THIS-IS-HTTP-STATUS-CODE,
	 * "status": "error"
	 * }
	 */
	public function saveListID( array $aParams ): array {
		$campaignID = $aParams['campaignID'] ?? '';
		$userID     = get_post_field('post_author', $campaignID);
		$aUserMeta  = $this->getCurrentUserMeta($userID);
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidIContactAPIKey',
			'IsValidIContactListID',
		], [
			'listID'      => $aParams['listID'] ?? '',
			'appID'       => $aUserMeta['appID'] ?? '',
			'appUsername' => $aUserMeta['appUsername'] ?? '',
			'appPassword' => $aUserMeta['appPassword'] ?? '',
			'campaignID'  => $aParams['campaignID'] ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$this->updateCampaignListIDConfiguration($campaignID, 'listID',
				$aResponse['data']['IsValidIContactListID']['id']);
			$this->updateCampaignListIDConfiguration($campaignID, 'listLabel',
				$aResponse['data']['IsValidIContactListID']['label']);

			return MessageFactory::factory()
				->success(esc_html__('Hoorays! Your list ID has been saved successfully.',
					'myshopkit'), $aResponse['data'])
				;
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @param array $aRequest
	 *
	 * @return array
	 *
	 *  * a successful response will return a array's object with message and status
	 *
	 * {
	 * "data": null,
	 * "message": "Hoorays! Your list ID has been saved successfully.",
	 * "status": "success"
	 * }
	 *
	 *
	 * a failure response will return array object with error message and status plus status code
	 *
	 * {
	 * "message": "THIS-IS-ERROR-MESSAGE",
	 * "code": THIS-IS-HTTP-STATUS-CODE,
	 * "status": "error"
	 * }
	 */
	public function subscribeEmailDirectly( array $aRequest ): array {
		$email      = $aRequest['email'] ?? '';
		$campaignID = $aRequest['postID'] ?? '';
		$userID     = $aRequest['userID'] ?? '';
		$name       = $aRequest['info']['name'] ?? '';

		$aUserMeta = $this->getCurrentUserMeta($userID);
		$aPostMeta = $this->getCurrentPostMeta($campaignID);
		$aResponse = $this->processMiddleware(
			[
				'IsIContactActivate',
				'IsValidEmail',
				'IsValidIContactAPIKey',
				'IsValidIContactListID',
			],
			[
				'appID'       => $aUserMeta['appID'] ?? '',
				'appUsername' => $aUserMeta['appUsername'] ?? '',
				'appPassword' => $aUserMeta['appPassword'] ?? '',
				'listID'      => $aPostMeta['listID'],
				'email'       => $email,
				'postID'      => $campaignID,
			]
		);

		if( $aResponse['status'] == 'success' ) {
			$aResponsePostEmail = IcontactConnection::connect(
				$aUserMeta['appID'],
				$aUserMeta['appUsername'],
				$aUserMeta['appPassword'])
				->addEmailToList(
					$email,
					$aPostMeta['listID'],
					$name
				);
			return MessageFactory::factory()->success($aResponsePostEmail['message']);
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}
}
