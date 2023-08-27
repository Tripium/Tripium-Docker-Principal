<?php

namespace WooKit\MailServices\Klaviyo\Controllers;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Klaviyo\Shared\KlaviyoConnection;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\Shared\Middleware\TraitMainMiddleware;

class KlaviyoController implements IMailService {
	use TraitMainMiddleware;

	public string $key         = 'klaviyo_info';
	public string $mailService = 'klaviyo';

	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;

	public function __construct() {
		add_action( WOOKIT_HOOK_PREFIX . 'after/subscribed', [$this, 'subscribeEmailDirectly' ] );
		add_filter( WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
			[ $this, 'configMiddleware' ] );
	}

	public function configMiddleware( array $aMiddleware ) {
		$aKlaviyo = include( plugin_dir_path( __FILE__ ) . "../Configs/Middleware.php" );

		return array_merge( $aMiddleware, $aKlaviyo );
	}

	public function getAllServiceData( $campaignID ): array {
		$aServiceData  = $this->getCurrentUserMeta( get_post_field( 'post_author', $campaignID ) );
		$aCampaignData = $this->getCurrentPostMeta( $campaignID );

		$selectStatus = $aServiceData['selected'];
		$aListData    = wp_parse_args(
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
				'publicApiKey'  => ! empty( $aServiceData['publicApiKey'] ) ?
					$this->protectValue( $aServiceData['publicApiKey'], 3 ) : null,
				'privateApiKey' => ! empty( $aServiceData['privateApiKey'] ) ?
					$this->protectValue( $aServiceData['privateApiKey'], 10 ) : null,
			],
			[
				'publicApiKey'  => '',
				'privateApiKey' => '',
			]
		);

		return MessageFactory::factory()->success( 'OK',
			[
				'campaignID'    => $campaignID,
				'aApiKeys'      => $aApiKeys,
				'activatedList' => $aListData['id'] == '' ? null : $aListData,
				'serviceName'   => $this->mailService,
				'status'        => $aCampaignData['status'],
				'selected'      => $selectStatus,
			]
		);
	}

	public function getAllLists(): array {
		$aUserMeta = $this->getCurrentUserMeta( get_current_user_id() );
		$aResponse = $this->processMiddleware(
			[
				'IsUserLoggedIn',
				'IsValidKlaviyoAPIKey',
			],
			[
				'publicApiKey'  => $aUserMeta['publicApiKey'] ?? '',
				'privateApiKey' => $aUserMeta['privateApiKey'] ?? '',
			]
		);

		if ( $aResponse['status'] == 'error' ) {
			return MessageFactory::factory()->response( $aResponse );
		}

		return MessageFactory::factory()->success(
			esc_html__( 'This is your lists.', 'myshopkit' ),
			[
				'items' => KlaviyoConnection::connect(
					$aUserMeta['privateApiKey'],
					$aUserMeta['publicApiKey']
				)->getLists()
			]
		);
	}

	public function saveApiKey( array $oRequest ): array {
		$aResponse = $this->processMiddleware( [
			'IsUserLoggedIn',
			'IsValidKlaviyoAPIKey',
		], [
			'publicApiKey'  => $publicApiKey = $oRequest['apiKeys']['publicApiKey'],
			'privateApiKey' => $privateApiKey = $oRequest['apiKeys']['privateApiKey'],
		] );
		if ( $aResponse['status'] == 'success' ) {
			$campaignID = $oRequest['campaignID'] ?? '';
			$status     = 'active';

			$this->updateCampaignAPIKeysConfiguration( 'publicApiKey', $publicApiKey );
			$this->updateCampaignAPIKeysConfiguration( 'privateApiKey', $privateApiKey );
			$this->updateCampaignAPIKeysConfiguration( 'selected', true );
			$this->updateCampaignListIDConfiguration( $campaignID, 'status', $status );

			$aListData         = KlaviyoConnection::connect( $privateApiKey, $publicApiKey )->getLists();
			$currentActiveList = $this->getCurrentPostMeta( $campaignID )['listID'];

			$aData = [
				'name'             => $this->mailService,
				'list'             => $aListData,
				'listItemSelected' => $currentActiveList,
				'status'           => $status,
				'selected'         => true,
				'apiKeys'          => [
					'publicApiKey'  => $this->protectValue( $publicApiKey, 3 ),
					'privateApiKey' => $this->protectValue( $privateApiKey, 10 ),
				],
			];

			return MessageFactory::factory()
			                     ->success(
				                     esc_html__( 'Hoorays! Your Api key has been saved successfully.', 'myshopkit' ),
				                     $aData
			                     );
		}

		return MessageFactory::factory()
		                     ->error( $aResponse['message'], $aResponse['code'] );
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

	public function saveListId( array $aParams ): array {
		$campaignID = $aParams['campaignID'];
		$userID     = get_post_field( 'post_author', $campaignID );
		$aUserMeta  = $this->getCurrentUserMeta( $userID );
		$aResponse  = $this->processMiddleware( [
			'IsUserLoggedIn',
			'IsValidKlaviyoAPIKey',
			'IsValidKlaviyoListID',
		], [
			'listID'        => $aParams['listID'] ?? '',
			'publicApiKey'  => $aUserMeta['publicApiKey'],
			'privateApiKey' => $aUserMeta['privateApiKey'],
			'campaignID'    => $campaignID = $aParams['campaignID'] ?? '',
		] );

		if ( $aResponse['status'] == 'success' ) {
			$aListInfo = KlaviyoConnection::connect( $aUserMeta['privateApiKey'], $aUserMeta['publicApiKey'] )
			                              ->getListInfo( $aParams['listID'] );

			$this->updateCampaignListIDConfiguration( $campaignID, 'listID', $aListInfo['data']['id'] );
			$this->updateCampaignListIDConfiguration( $campaignID, 'listLabel', $aListInfo['data']['label'] );

			return MessageFactory::factory()->success(
				esc_html__( 'Hoorays! Your list Id has been saved successfully', 'myshopkit' ),
				$aListInfo['data']
			);
		}

		return MessageFactory::factory()
		                     ->error( $aResponse['message'], $aResponse['code'] );
	}

	/**
	 * @param array $aInfo {email: string, postID: string, userID: string}
	 *
	 * @return array
	 */
	public function subscribeEmailDirectly( array $aInfo ): array {
		$email      = $aInfo['email'];
		$campaignID = $aInfo['postID'];
		$userID     = $aInfo['userID'];

		$aUserMeta = $this->getCurrentUserMeta( $userID );
		$aPostMeta = $this->getCurrentPostMeta( $campaignID );
		$aResponse = $this->processMiddleware(
			[
				'IsKlaviyoActivate',
				'IsValidEmail',
				'IsValidKlaviyoAPIKey',
				'IsValidKlaviyoListID',
			],
			[
				'publicApiKey'  => $aUserMeta['publicApiKey'] ?? '',
				'privateApiKey' => $aUserMeta['privateApiKey'] ?? '',
				'listID'        => $listID = $aPostMeta['listID'],
				'email'         => $email,
				'postID'        => $campaignID,
			]
		);

		if ( $aResponse['status'] == 'success' ) {
			$aResponse = KlaviyoConnection::connect( $aUserMeta['privateApiKey'], $aUserMeta['privateApiKey'] )
			                              ->addEmailToList( $email, $listID );
			return MessageFactory::factory()->response( $aResponse );
		}

		return MessageFactory::factory()->error( $aResponse['message'], $aResponse['code'] );
	}
}
