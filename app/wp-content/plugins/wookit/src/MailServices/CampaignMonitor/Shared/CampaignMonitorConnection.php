<?php

namespace WooKit\MailServices\CampaignMonitor\Shared;

use CS_REST_Clients;
use CS_REST_Subscribers;
use WooKit\Illuminate\Message\MessageFactory;

class CampaignMonitorConnection{
	private static ?CampaignMonitorConnection $oSelf = null;
	private static ?CS_REST_Clients $oConnect;

	/**
	 * @param string $apiKey
	 * @param string $clientID
	 * @return CampaignMonitorConnection
	 */
	public static function connect( string $apiKey, string $clientID ): CampaignMonitorConnection {
		if( self::$oSelf === null ) {
			self::$oSelf = new static();
		}

		self::$oConnect = new CS_REST_Clients($clientID, ['api_key' => $apiKey]);
		return self::$oSelf;
	}

	public function getLists(): array {
		return self::$oConnect->get_lists()->response;
	}

	private function findListInLists( $listID, $aLists ): array {
		foreach( $aLists as $aList ) {
			if( $aList->ListID == $listID ) {
				return [
					'id'    => $aList->ListID,
					'label' => $aList->Name,
				];
			}
		}

		return [];
	}

	public function getListInfo( $listID ) {
		$aLists = self::getLists();
		if( empty($aLists) ) {
			return MessageFactory::factory()->error('We could not find the list info', 404);
		}

		$aList = $this->findListInLists($listID, $aLists);

		if( empty($aList) ) {
			return MessageFactory::factory()->error('We could not find the list info', 404);
		}

		return MessageFactory::factory()->success('We found your list info', $aList);
	}

	public function addEmailToList( string $email, string $apiKey, string $listID ): array {
		$wrap   = new CS_REST_Subscribers($listID, ['api_key' => $apiKey]);
		$result = $wrap->add([
			'EmailAddress'   => $email,
			'Name'           => 'subscriber',
			'CustomFields'   => [],
			'ConsentToTrack' => 'yes',
			'Resubscribe'    => true,
		]);
		if( $result->was_successful() ) {
			return MessageFactory::factory()
				->success(
					esc_html__('Hoorays! Your email has been saved successfully.',
						'myshopkit')
				);
		}

		return MessageFactory::factory()
			->error(
				esc_html__('Oops! Look like we meet a problem. Please re-check it!',
					'myshopkit'),
				$result->http_status_code
			);
	}
}
