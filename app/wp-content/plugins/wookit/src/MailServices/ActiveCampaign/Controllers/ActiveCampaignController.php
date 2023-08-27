<?php

namespace WooKit\MailServices\ActiveCampaign\Controllers;

use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\MailServices\ActiveCampaign\Shared\ActiveCampaignConnection;

class ActiveCampaignController implements IMailService{
	use TraitMainMiddleware;
	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;

	public string $key = 'active_campaign_info';
	public string $mailService = 'activecampaign';

	public function __construct() {
		add_action(WOOKIT_HOOK_PREFIX . 'after/subscribed', [$this, 'subscribeEmailDirectly']);
		add_filter(WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
			[$this, 'configMiddleware']);
	}

	public function configMiddleware( array $aMiddleware ) {
		$aActiveCampaignMiddlewares = include(plugin_dir_path(__FILE__) . "../Configs/Middlewares.php");

		return array_merge($aMiddleware, $aActiveCampaignMiddlewares);
	}

	public function getAllServiceData( $campaignID ): array {
		$aServiceData  = $this->getCurrentUserMeta(get_current_user_id());
		$aCampaignData = $this->getCurrentPostMeta($campaignID);
		$selectStatus  = $aServiceData['selected'];
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
				'apiKey' => !empty($aServiceData['apiKey'])  ? $this->protectValue($aServiceData['apiKey'], 10) :
					null,
				'url'    => !empty($aServiceData['url']) ? $this->protectValue($aServiceData['url'], 15) : null,
			],
			[
				'apiKey' => '',
				'url'    => '',
			]
		);

		return MessageFactory::factory()->success('OK',
			[
				'serviceName'   => $this->mailService,
				'activatedList' => $aListData['id'] == '' ? null : $aListData,
				'aApiKeys'      => $aApiKeys,
				'status'        => $aCampaignData['status'],
				'selected'      => $selectStatus,
			]
		)
			;
	}

	/**
	 * @param array $aParams
	 *
	 * @return array
	 */
	public function saveApiKey( array $aParams ): array {
		$apiKey     = $aParams['apiKeys']['apiKey'] ?? '';
		$url        = $aParams['apiKeys']['url'] ?? '';
		$campaignID = $aParams['campaignID'] ?? '';

		$aResponse = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidActiveCampaignAPIKey',
		], [
			'apiKey' => $apiKey,
			'url'    => $url,
		]);

		if( $aResponse['status'] == 'success' ) {
			$postMetaData      = $this->getCurrentPostMeta($campaignID);
			$listData          = $this->getAllLists();
			$currentActiveList = !isset($postMetaData['listID'])
				? null : [
					'id'    => $postMetaData['listID'],
					'label' => $postMetaData['listLabel'],
				];
			$status            = 'active';
			$this->updateCampaignAPIKeysConfiguration('apiKey', $apiKey);
			$this->updateCampaignAPIKeysConfiguration('url', $url);
			$this->updateCampaignAPIKeysConfiguration('campaignID', $campaignID);
			$this->updateCampaignAPIKeysConfiguration('selected', true);
			$this->updateCampaignListIDConfiguration($campaignID, 'status', $status);
			$aData = [
				'name'             => $this->mailService,
				'list'             => $listData,
				'listItemSelected' => $currentActiveList,
				'status'           => $status,
				'selected'         => true,
				'apiKeys'          => [
					'apiKey' => $this->protectValue($apiKey, 10),
					'url'    => $this->protectValue($url, 15),
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

	/**
	 * @return array
	 */
	public function getAllLists(): array {
		$aUserMeta = $this->getCurrentUserMeta(get_current_user_id());
		$aResponse = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidActiveCampaignAPIKey',
		], [
			'apiKey' => $aUserMeta['apiKey'],
			'url'    => $aUserMeta['url'],
		]);
		if( $aResponse['status'] == 'success' ) {
			$aResponse = ActiveCampaignConnection::connect($aUserMeta['apiKey'], $aUserMeta['url'])->getLists();

			return MessageFactory::factory()->response($aResponse);
		}
		return MessageFactory::factory()->response($aResponse);
	}

	/**
	 * @param array $aParams
	 *
	 * @return array
	 */
	public function saveListID( array $aParams ): array {
		$listID     = $aParams['listID'] ?? '';
		$campaignID = $aParams['campaignID'] ?? '';
		$userID     = get_post_field('post_author', $campaignID);
		$aUserMeta  = $this->getCurrentUserMeta($userID);
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidActiveCampaignAPIKey',
			'IsValidActiveCampaignListID',
		], [
			'listID'     => $listID,
			'apiKey'     => $aUserMeta['apiKey'],
			'url'        => $aUserMeta['url'],
			'campaignID' => $campaignID ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$this->updateCampaignListIDConfiguration($campaignID, 'listID', $aResponse['data']['IsValidActiveCampaignListID']['id']);
			$this->updateCampaignListIDConfiguration($campaignID, 'listLabel', $aResponse['data']['IsValidActiveCampaignListID']['label']);

			return MessageFactory::factory()
				->success(
					esc_html__('Hoorays! Your list ID has been saved successfully',
						'myshopkit'),
					$aResponse['data']['IsValidActiveCampaignListID']
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
		$aResponse = $this->saveServiceStatus($aParams['campaignID'] ?? '', $aParams['status'] ?? '');
		if( $aResponse['status'] == 'success' ) {
			return MessageFactory::factory()->success($aResponse['message']);
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @param array $aParams
	 *
	 * @return array
	 * @throws Exception
	 */
	public function subscribeEmailDirectly( array $aParams ): array {
		$email      = $aParams['email'] ?? '';
		$campaignID = $aParams['postID'];
		$userID     = $aParams['userID'];
		$aInfo      = $aParams['info'];
		$aPostMeta  = $this->getCurrentPostMeta($campaignID);
		$aUserMeta  = $this->getCurrentUserMeta($userID);
		$apiKey     = $aUserMeta['apiKey'] ?? '';
		$url        = $aUserMeta['url'] ?? '';
		$listID     = $aPostMeta['listID'] ?? '';
		$name       = $aInfo['name'] ?? '';
		$aResponse  = $this->processMiddleware(
			[
				'IsActiveCampaignActivate',
				'IsValidEmail',
				'IsValidActiveCampaignAPIKey',
				'IsValidActiveCampaignListID',
			],
			[
				'apiKey' => $apiKey,
				'url'    => $url,
				'listID' => $listID,
				'email'  => $email,
				'postID' => $campaignID,
			]
		);
		if( $aResponse['status'] == 'success' ) {
			$aResponse = ActiveCampaignConnection::connect($apiKey, $url)->addEmailToList($email, $listID, $name);
			return MessageFactory::factory()->response($aResponse);
		}
		return MessageFactory::factory()->response($aResponse);
	}
}
