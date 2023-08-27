<?php

namespace WooKit\MailServices\CampaignMonitor\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\MailServices\CampaignMonitor\Shared\CampaignMonitorConnection;

class CampaignMonitorController implements IMailService{
	use TraitMainMiddleware;

	public string $key = 'campaign_monitor_info';
	public string $mailService = 'campaignmonitor';

	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;

	public function __construct() {
		add_action(WOOKIT_HOOK_PREFIX . 'after/subscribed', [$this, 'subscribeEmailDirectly']);
		add_filter(WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
			[$this, 'configMiddleware']);
	}

	public function configMiddleware( array $aMiddleware ) {
		$aCampaignMonitorMiddlewares = include(plugin_dir_path(__FILE__) . "../Configs/Middlewares.php");

		return array_merge($aMiddleware, $aCampaignMonitorMiddlewares);
	}

	public function getAllServiceData( $campaignID ): array {
		$aServiceData = $this->getCurrentUserMeta(get_current_user_id());
		$aLists       = $this->getCurrentPostMeta($campaignID);
		$selectStatus = $aServiceData['selected'];
		$aListData    = wp_parse_args(
			[
				'id'    => $aLists['listID'] ?? '',
				'label' => $aLists['listLabel'] ?? '',
			],
			[
				'id'    => '',
				'label' => '',
			]
		);

		$aApiKeys = wp_parse_args(
			[
				'apiKey'   => !empty($aServiceData['apiKey']) ?
					$this->protectValue($aServiceData['apiKey'], 10) :
					null,
				'clientID' => !empty($aServiceData['clientID']) ?
					$this->protectValue($aServiceData['clientID'], 10) : null,
			],
			[
				'apiKey'   => '',
				'clientID' => '',
			]
		);

		return MessageFactory::factory()->success('OK',
			[
				'aListData'     => $aListData['id'] == '' ? null : $aListData,
				'aApiKeys'      => $aApiKeys,
				'serviceName'   => $this->mailService,
				'activatedList' => $aListData['id'] == '' ? null : $aListData,
				'status'        => $aLists['status'],
				'selected'      => $selectStatus,
			]
		)
			;
	}

	public function saveApiKey( array $aParams ): array {
		$apiKey     = $aParams['apiKeys']['apiKey'] ?? '';
		$clientID   = $aParams['apiKeys']['clientID'] ?? '';
		$campaignID = $aParams['campaignID'] ?? '';
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidCampaignMonitorAPIKey',
		], [
			'apiKey'   => $apiKey ?? '',
			'clientID' => $clientID ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$aPostMeta         = $this->getCurrentPostMeta($campaignID);
			$aListData         = $this->getAllLists()['data'] ?? [];
			$aLists            = !isset($aListData['items']) ? null : $aListData['items'];
			$currentActiveList = (empty($aPostMeta['listID']) || is_null($aLists))  ? null : $aPostMeta['listID'];
			$status            = 'active';
			$this->updateCampaignAPIKeysConfiguration('apiKey', $apiKey);
			$this->updateCampaignAPIKeysConfiguration('clientID', $clientID);
			$this->updateCampaignAPIKeysConfiguration('selected', true);
			$this->updateCampaignListIDConfiguration($campaignID, 'status', $status);
			$aData = [
				'name'             => $this->mailService,
				'list'             => $aLists,
				'listItemSelected' => $currentActiveList,
				'status'           => $status,
				'selected'         => true,
				'apiKeys'          => [
					'apiKey'   => $this->protectValue($apiKey, 10),
					'clientID' => $this->protectValue($clientID, 10),
				],
			];

			return MessageFactory::factory()
				->success(esc_html__('Hoorays! Your Api key has been saved successfully.',
					'myshopkit'),

					$aData
				)
				;
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	public function getAllLists(): array {
		$aUserMeta = $this->getCurrentUserMeta(get_current_user_id());
		$aResponse = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidCampaignMonitorAPIKey',
		], [
			'apiKey'   => $aUserMeta['apiKey'] ?? '',
			'clientID' => $aUserMeta['clientID'] ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$aLists = CampaignMonitorConnection::connect($aUserMeta['apiKey'], $aUserMeta['clientID'])->getLists();
			if( !empty($aLists) ) {
				$aFilteredLists = array_map(function( $aLists ) {
					return [
						'id'    => $aLists->ListID,
						'label' => $aLists->Name,
					];
				}, $aLists);
				return MessageFactory::factory()
					->success(
						esc_html__('This is your lists.', 'myshopkit'),
						[
							'items' => $aFilteredLists,
						]
					)
					;
			}

			return MessageFactory::factory()
				->error(
					esc_html__('Oops! Look like you don\'t have any lists yet. Please create one.',
						'myshopkit'), 400
				)
				;
		}
		return MessageFactory::factory()->response($aResponse);
	}

	public function saveListID( array $aParams ): array {
		$campaignID = $aParams['campaignID'] ?? '';
		$userID     = get_post_field('post_author', $campaignID);
		$aUserMeta  = $this->getCurrentUserMeta($userID);
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidCampaignMonitorAPIKey',
			'IsValidCampaignMonitorListID',
		], [
			'listID'     => $aParams['listID'] ?? '',
			'apiKey'     => $aUserMeta['apiKey'] ?? '',
			'clientID'   => $aUserMeta['clientID'] ?? '',
			'campaignID' => $aParams['campaignID'] ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$this->updateCampaignListIDConfiguration($campaignID, 'listID', $aResponse['data']['IsValidCampaignMonitorListID']['id']);
			$this->updateCampaignListIDConfiguration($campaignID, 'listLabel', $aResponse['data']['IsValidCampaignMonitorListID']['label']);

			return MessageFactory::factory()
				->success(esc_html__('Hoorays! Your list ID has been saved successfully.',
					'myshopkit'), $aResponse['data']['IsValidCampaignMonitorListID'])
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
		$aResponse = $this->saveServiceStatus($aParams['campaignID'], $aParams['status']);
		if( $aResponse['status'] == 'success' ) {
			return MessageFactory::factory()->success($aResponse['message']);
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	public function subscribeEmailDirectly( array $aParams ): array {
		$email      = $aParams['email'] ?? '';
		$campaignID = $aParams['postID'] ?? '';
		$userID     = $aParams['userID'] ?? '';

		$aUserMeta = $this->getCurrentUserMeta($userID);
		$aPostMeta = $this->getCurrentPostMeta($campaignID);
		$aResponse = $this->processMiddleware(
			[
				'IsCampaignMonitorActive',
				'IsValidEmail',
				'IsValidCampaignMonitorAPIKey',
				'IsValidCampaignMonitorListID',
			],
			[
				'apiKey'   => $aUserMeta['apiKey'] ?? '',
				'clientID' => $aUserMeta['clientID'] ?? '',
				'listID'   => $aPostMeta['listID'] ?? '',
				'email'    => $email,
				'postID'   => $campaignID,
			]
		);
		if( $aResponse['status'] == 'success' ) {
			$aResponse = CampaignMonitorConnection::connect($aUserMeta['apiKey'], $aUserMeta['clientID'])
				->addEmailToList($email, $aUserMeta['apiKey'], $aPostMeta['listID'])
			;
			return MessageFactory::factory()->response($aResponse);
		}
		return MessageFactory::factory()->response($aResponse);
	}
}
