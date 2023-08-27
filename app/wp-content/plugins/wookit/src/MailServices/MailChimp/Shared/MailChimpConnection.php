<?php


namespace WooKit\MailServices\MailChimp\Shared;

// Tac de dung chung Middleware va Controller
use GuzzleHttp\Exception\ClientException;
use MailchimpMarketing\ApiClient;
use MailchimpMarketing\Configuration;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\MultiLanguage\MultiLanguage;

class MailChimpConnection {
	private static ?ApiClient           $oClient;
	private static ?MailChimpConnection $oSelf;

	/**
	 * get server from api key
	 *
	 * @param $apiKey
	 *
	 * @return string
	 */
	private static function getServer( $apiKey ): string {
		$aServerInfo = explode( '-', $apiKey );

		return $aServerInfo[1] ?? '';
	}

	private function makeConnect( $apiKey ) {
		self::$oClient = new ApiClient();
		$server        = self::getServer( $apiKey );

		self::$oClient->setConfig( [
			'apiKey' => $apiKey,
			'server' => $server,
		] );
	}

	public static function connect( string $apiKey ): MailChimpConnection {
		if (empty( self::$oSelf )) {
			self::$oSelf = new static();
		}

		self::$oSelf->makeConnect( $apiKey );

		return self::$oSelf;
	}

	public function ping(): bool {
		try {
			self::$oClient->ping->getWithHttpInfo();

			return true;
		}
		catch ( ClientException $oException ) {
			return false;
		}
	}

	public function addEmailToList( $email, $listID ): array {
		try {
			self::$oClient->lists->addListMember( $listID, [
				'email_address' => $email,
				'status'        => 'subscribed',
			] );

			return MessageFactory::factory()
			                     ->success( esc_html__(
				                     'Email is added to the list successfully',
				                     'myshopkit'
			                     ) );
		}
		catch ( \Exception $oException ) {
			return MessageFactory::factory()
			                     ->error( 'Oops! This email address has already subscribed.', $oException->getCode() );
		}
	}

	private function findListInLists( $listID, $aLists ): array {
		foreach ( $aLists as $aList ) {
			if ( $aList['id'] == $listID ) {
				return $aList;
			}
		}

		return [];
	}

	public function getListInfo( $listID ) {
		$aLists = self::getLists();
		if ( empty( $aLists ) ) {
			return MessageFactory::factory()->error( 'We could not find the list info', 404 );
		}

		$aList = $this->findListInLists( $listID, $aLists );

		if ( empty( $aList ) ) {
			return MessageFactory::factory()->error( 'We could not find the list info', 404 );
		}

		return MessageFactory::factory()->success( 'We found your list info', $aList );
	}

	public function getLists(): array {
		$aLists = self::$oClient->lists->getAllLists()->lists;
		if ( empty( $aLists ) ) {
			return [];
		};

		return array_map( function ( $aLists ) {
			return
				[
					'label' => $aLists->name,
					'id'    => $aLists->id,
				];
		}, $aLists );
	}
}
