<?php

namespace WooKit\MailServices\Klaviyo\Shared;

use Klaviyo\Klaviyo as Klaviyo;
use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Model\ProfileModel as KlaviyoProfile;
use WooKit\Illuminate\Message\MessageFactory;

class KlaviyoConnection {
	private static ?KlaviyoConnection $oSelf = null;
	private static ?Klaviyo           $oConnect;

	public static function connect( string $privateApiKey, string $publicApiKey ): KlaviyoConnection {
		if ( self::$oSelf === null ) {
			self::$oSelf = new static();
		}

		self::$oConnect = new Klaviyo( $privateApiKey, $publicApiKey );

		return self::$oSelf;
	}

	public function ping(): bool {
		try {
			self::$oConnect->lists->getLists();

			return true;
		}
		catch ( \Exception $oException ) {
			return false;
		}
	}

	private function findListInLists( $listID, $aLists ): array {
		if ( ! empty( $aLists ) ) {
			foreach ( $aLists as $aList ) {
				if ( $aList['id'] == $listID ) {
					return $aList;
				}
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

	/**
	 * @return array
	 */
	public function getLists(): array {
		try {
			$aLists = self::$oConnect->lists->getLists();

			return array_map( function ( $aLists ) {
				return [
					'label' => $aLists['list_name'],
					'id'    => $aLists['list_id'],
				];
			}, $aLists );

		}
		catch ( \Exception $oException ) {
			return [];
		}
	}

	public function addEmailToList( $email, $listID ): array {
		try {
			$addProfile = [
				new KlaviyoProfile(
					[
						'$email' => $email
					]
				),
			];

			self::$oConnect->lists->addMembersToList( $listID, $addProfile );

			return MessageFactory::factory()
			                     ->success( esc_html__( 'Hoorays! Your email is added to the list successfully',
				                     'myshopkit' ) );
		}
		catch ( KlaviyoException $oException ) {
			return MessageFactory::factory()
			                     ->error( esc_html__( 'Oops! Look like we meet some error. Please re-check it.',
				                     'myshopkit' ), 400 );
		}
	}
}

