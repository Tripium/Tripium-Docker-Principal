<?php

namespace WooKit\MailServices\GetResponse\Controllers;

use Getresponse\Sdk\Client\GetresponseClient;
use Getresponse\Sdk\GetresponseClientFactory;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use Getresponse\Sdk\Operation\Campaigns\GetCampaigns\GetCampaigns;
use Getresponse\Sdk\Client\Exception\MalformedResponseDataException;
use WooKit\MailServices\GetResponse\Shared\GetResponseConnection;

class GetResponseController implements IMailService{

	public string $key = 'get_response_info';
	public string $mailService = 'getresponse';

	use TraitGenerateRestEndpoint;
	use TraitMainMiddleware;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;

	public function __construct() {
		add_action(WOOKIT_HOOK_PREFIX . 'after/subscribed', [$this, 'subscribeEmailDirectly']);
		add_filter(WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
			[$this, 'configMiddleware']);
	}

	public function configMiddleware( array $aMiddleware ) {
		$aKlaviyo = include(plugin_dir_path(__FILE__) . "../Configs/Middlewares.php");

		return array_merge($aMiddleware, $aKlaviyo);
	}

	public function getAllServiceData( $campaignID ): array {
		$aServiceData = $this->getCurrentUserMeta(get_current_user_id());
		$aLists       = $this->getCurrentPostMeta($campaignID);
		$selectStatus = $aServiceData['selected'];
		$aListData    = wp_parse_args(
			[
				'id'    => $aLists['listID'],
				'label' => $aLists['listLabel'],
			],
			[
				'id'    => '',
				'label' => '',
			]
		);

		$aApiKeys = wp_parse_args(
			[
				'apiKey' => !empty($aServiceData['apiKey']) ? $this->protectValue($aServiceData['apiKey'], 10) :
					null,
			],
			[
				'apiKey' => '',
			]
		);

		return MessageFactory::factory()->success('OK',
			[
				'aApiKeys'      => $aApiKeys,
				'serviceName'   => $this->mailService,
				'campaignID'    => $campaignID,
				'activatedList' => $aListData['id'] == '' ? null : $aListData,
				'status'        => $aLists['status'],
				'selected'      => $selectStatus,
			]
		)
			;
	}

	public function connectGetResponse(): GetresponseClient {
		return GetresponseClientFactory::createWithApiKey($this->getCurrentUserMeta(get_current_user_id())['apiKey']);
	}

	public function checkIsUserApiKeyValid( ?string $apiKey = '' ): array {
		if( empty($apiKey) ) {
			$apiKey = $this->getCurrentUserMeta(get_current_user_id())['apiKey'];
		}
		if( !empty($apiKey) ) {
			$client             = GetresponseClientFactory::createWithApiKey($apiKey);
			$campaignsOperation = new GetCampaigns();
			$response           = $client->call($campaignsOperation);
			if( $response->isSuccess() ) {
				return MessageFactory::factory()->success('OK',
					[
						'apiKey' => $apiKey,
					]
				)
					;
			}

			return MessageFactory::factory()
				->error(esc_html__('Oops! Look like your api key is invalid. Please check again!',
					'wookit'), 400)
				;
		}

		return MessageFactory::factory()
			->error(esc_html__('Oops! Look like you haven\'t give us your api key yet. Please check again!'),
				400)
			;
	}

	/**
	 * @throws MalformedResponseDataException
	 */
	public function saveApiKey( array $aParams ): array {
		$campaignID = $aParams['campaignID'] ?? '';
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidGetResponseAPIKey',
		], [
			'apiKey' => $apiKey = $aParams['apiKeys']['apiKey'] ?? '',
		]);

		if( $aResponse['status'] == 'success' ) {
			$status   = 'active';
			$postMeta = $this->getCurrentPostMeta($campaignID);
			$this->updateCampaignAPIKeysConfiguration('apiKey', $apiKey);
			$this->updateCampaignAPIKeysConfiguration('selected', true);
			$this->updateCampaignListIDConfiguration($campaignID, 'status', $status);
			$aListData         = $this->getAllLists()['data'];
			$currentActiveList = empty($postMeta['listID'])
				? null : $postMeta['listID'];
			$aLists            = empty($aListData['items'])
				? null : $aListData['items'];
			$aData             = [
				'name'             => $this->mailService,
				'lists'            => $aLists,
				'listItemSelected' => $currentActiveList,
				'status'           => $status,
				'apiKeys'          => [
					'apiKey' => $this->protectValue($apiKey, 10),
				],
			];

			return MessageFactory::factory()
				->success(esc_html__('Hoorays! Your Api key has been saved successfully.',
					'wookit'), $aData
				)
				;
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @throws MalformedResponseDataException
	 */
	public function getAllLists(): array {
		$aResponse = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidGetResponseAPIKey',
		], [
			'apiKey' => $apiKey = $this->getCurrentUserMeta(get_current_user_id())['apiKey'] ?? '',
		]);

		if( $aResponse['status'] == 'success' ) {
			$aLists = GetResponseConnection::connect($apiKey)->getLists();

			return MessageFactory::factory()
				->success(
					esc_html__('This is your lists.', 'wookit'),
					[
						'items' => $aLists,
					]
				)
				;
		}
		return MessageFactory::factory()->response($aResponse);
	}

	/**
	 * @param array $aParams
	 *
	 * @return array
	 */
	public function changeServiceStatus( array $aParams ): array {
		$status     = $aParams['status'] ?? '';
		$campaignID = $aParams['campaignID'] ?? '';
		$aResponse  = $this->saveServiceStatus($campaignID, $status);
		if( $aResponse['status'] == 'success' ) {
			return MessageFactory::factory()->success($aResponse['message']);
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @throws MalformedResponseDataException
	 */
	public function saveListID( array $aParams ): array {
		$campaignID = $aParams['campaignID'] ?? '';
		$userID     = get_post_field('post_author', $campaignID);
		$aUserMeta  = $this->getCurrentUserMeta($userID);
		$aResponse  = $this->processMiddleware([
			'IsUserLoggedIn',
			'IsValidGetResponseAPIKey',
			'IsValidGetResponseListID',
		], [
			'listID'     => $aParams['listID'] ?? '',
			'apiKey'     => $aUserMeta['apiKey'] ?? '',
			'campaignID' => $aParams['campaignID'] ?? '',
		]);
		if( $aResponse['status'] == 'success' ) {
			$listData = $aResponse['data']['IsValidGetResponseListID'];
			$this->updateCampaignListIDConfiguration($campaignID, 'listID', $listData['id']);
			$this->updateCampaignListIDConfiguration($campaignID, 'listLabel', $listData['label']);

			return MessageFactory::factory()
				->success(esc_html__('Hoorays! Your list id has been save successfully.',
					'wookit'), $listData)
				;
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

	/**
	 * @throws MalformedResponseDataException
	 */
	public function subscribeEmailDirectly( array $aRequest ) {
		$email      = $aRequest['email'] ?? '';
		$campaignID = $aRequest['postID'] ?? '';
		$userID     = $aRequest['userID'] ?? '';

		$aUserMeta = $this->getCurrentUserMeta($userID);
		$aPostMeta = $this->getCurrentPostMeta($campaignID);
		$aResponse = $this->processMiddleware(
			[
				'IsGetResponseActivate',
				'IsValidEmail',
				'IsValidGetResponseAPIKey',
				'IsValidGetResponseListID',
			],
			[
				'apiKey' => $aUserMeta['apiKey'] ?? '',
				'listID' => $aPostMeta['listID'] ?? '',
				'email'  => $email ?? '',
				'postID' => $campaignID ?? '',
			]
		);

		if( $aResponse['status'] == 'success' ) {
			$aResponseCheckEmail = GetResponseConnection::connect($aUserMeta['apiKey'])
				->addEmailToList($email, $aResponse['data']['IsValidGetResponseListID']['id'])
			;

			return MessageFactory::factory()->response($aResponseCheckEmail);
		}

		return MessageFactory::factory()->error($aResponse['message'], $aResponse['code']);
	}

}


