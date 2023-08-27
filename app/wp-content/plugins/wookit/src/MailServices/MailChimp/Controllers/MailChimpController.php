<?php

namespace WooKit\MailServices\MailChimp\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\MailServices\MailChimp\Shared\MailChimpConnection;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\Shared\Middleware\TraitMainMiddleware;

class MailChimpController implements IMailService {

	use TraitMainMiddleware;

	public string $key         = 'mailchimp_info';
	public string $mailService = 'mailchimp';

	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;

	protected $client;

	public function __construct() {
		add_action( WOOKIT_HOOK_PREFIX . 'after/subscribed', [$this, 'subscribeEmailDirectly' ] );
		add_filter(
			WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
			[
				$this,
				'addMailChimpMiddleware'
			]
		);
	}

	public function addMailChimpMiddleware( $aMiddleware ): array {
		$aMailChimpMiddleware = include plugin_dir_path( __FILE__ ) . '../Configs/Middleware.php';

		return array_merge( $aMailChimpMiddleware, $aMiddleware );
	}

	/**
	 * @param array $aParams
	 *
	 * @return array
	 */
	public function changeServiceStatus( array $aParams ): array {
		$aResponse = $this->saveServiceStatus( $aParams['campaignID'] ?? '', $aParams['status'] ?? '' );
		if ( $aResponse['status'] == 'success' ) {
			return MessageFactory::factory()->success( $aResponse['message'] );
		}

		return MessageFactory::factory()->error( $aResponse['message'], $aResponse['code'] );
	}

	/**
	 * Checking the configurations is valid to update user api key
	 *
	 * @param array $aRequest
	 *
	 * @return array
	 */
	public function saveApiKey( array $aRequest ): array {
		$apiKey     = $aRequest['apiKeys']['apiKey'] ?? '';
		$campaignID = $aRequest['campaignID'] ?? '';

		$aValidation = $this->processMiddleware( [
			'IsUserLoggedIn',
			'IsValidMailChimpAPIKey'
		], [
			'apiKey' => $apiKey
		] );

		if ( $aValidation['status'] == 'success' ) {
			$aLists       = MailChimpConnection::connect( $apiKey )->getLists();
			$selectedList = $this->getCurrentPostMeta( $campaignID )['listID'];
			$status       = 'active';

			$this->updateCampaignAPIKeysConfiguration( 'apiKey', $apiKey );
			$this->updateCampaignAPIKeysConfiguration( 'selected', true );
			$this->updateCampaignListIDConfiguration( $campaignID, 'status', $status );

			$aData = [
				'name'             => $this->mailService,
				'list'             => ! empty( $aLists ) ? $aLists : null,
				'listItemSelected' => $selectedList,
				'status'           => $status,
				'selected'         => true,
				'apiKeys'          => [
					'apiKey' => $this->protectValue( $apiKey, 10 ),
				],
			];

			return MessageFactory::factory()
			                     ->success( esc_html__( 'Hoorays! Your Api key has been saved successfully.',
				                     'myshopkit' ), $aData
			                     );
		}

		return MessageFactory::factory()
		                     ->error( $aValidation['message'], $aValidation['code'] );
	}


	/**
	 * Get all lists
	 *
	 * @return array
	 */
	public function getAllLists(): array {
		$aValidation = $this->processMiddleware( [
			'IsUserLoggedIn',
			'IsValidMailChimpAPIKey'
		], [
			'apiKey' => $apiKey = $this->getCurrentUserMeta( get_current_user_id() )['apiKey']
		] );

		if ( $aValidation['status'] == 'error' ) {
			return MessageFactory::factory()->response( $aValidation );
		}

		return MessageFactory::factory()->success(
			esc_html__( 'This is your lists.', 'myshopkit' ),
			[
				'items' => MailChimpConnection::connect( $apiKey )->getLists()
			]
		);
	}

	public function getAllServiceData( $campaignID ): array {
		$aServiceData  = $this->getCurrentUserMeta( get_current_user_id() );
		$aCampaignData = $this->getCurrentPostMeta( $campaignID );
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
				'apiKey' => ! empty( $aServiceData['apiKey'] ) ? $this->protectValue( $aServiceData['apiKey'], 10 ) :
					null,
			],
			[
				'apiKey' => '',
			]
		);

		return MessageFactory::factory()->success( 'OK',
			[
				'aApiKeys'      => $aApiKeys,
				'activatedList' => $aListData['id'] == '' ? null : $aListData,
				'serviceName'   => $this->mailService,
				'status'        => $aCampaignData['status'],
				'selected'      => $selectStatus,
			]
		);
	}

	/**
	 * checking the Request is valid to save User listID
	 *
	 * @param array $oRequest
	 *
	 * @return array
	 */
	public function saveListId( array $oRequest ): array {
		$listID     = $oRequest['listID'] ?? '';
		$campaignID = $oRequest['campaignID'] ?? '';

		$aValidation = $this->processMiddleware( [
			'IsUserLoggedIn',
			'IsValidMailChimpAPIKey',
			'IsValidMailChimpListID'
		], [
			'apiKey' => $apiKey = $this->getCurrentUserMeta( get_current_user_id() )['apiKey'],
			'listID' => $listID
		] );

		if ( $aValidation['status'] == 'error' ) {
			return MessageFactory::factory()->response( $aValidation );
		}

		/**
		 * @var $aList array{id: string, label: string}
		 */
		$aList = MailChimpConnection::connect( $apiKey )->getListInfo( $listID )['data'];
		$this->updateCampaignListIDConfiguration( $campaignID, 'listID', $aList['id'] );
		$this->updateCampaignListIDConfiguration( $campaignID, 'listLabel', $aList['label'] );

		return MessageFactory::factory()->success(
			esc_html__( 'Hoorays! Your list Id has been saved successfully.', 'myshopkit' ),
			$aList
		);

	}

	public function subscribeEmailDirectly( array $aInfo ): array {
		$apiKey = $this->getCurrentUserMeta( $aInfo['userID'] )['apiKey'];
		$listID = $this->getCurrentPostMeta( $aInfo['postID'] )['listID'];

		$aResponse = $this->processMiddleware(
			[
				'IsMailChimpActivate',
				'IsValidEmail',
				'IsValidMailChimpAPIKey',
				'IsValidMailChimpListID'
			],
			[
				'email'  => $aInfo['email'],
				'apiKey' => $apiKey,
				'listID' => $listID,
				'postID' => $aInfo['postID']
			]
		);

		if ( $aResponse['status'] == 'error' ) {
			return MessageFactory::factory()->response( $aResponse );
		}

		return MessageFactory::factory()->response( MailChimpConnection::connect( $apiKey )
		                                                               ->addEmailToList( $aInfo['email'], $listID ) );
	}
}
