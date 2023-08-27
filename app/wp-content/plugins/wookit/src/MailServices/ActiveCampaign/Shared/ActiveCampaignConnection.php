<?php

namespace WooKit\MailServices\ActiveCampaign\Shared;

use Exception;
use TestMonitor\ActiveCampaign\ActiveCampaign;
use WooKit\Illuminate\Message\MessageFactory;

class ActiveCampaignConnection{

	private static ?ActiveCampaignConnection $oSelf = null;
	private static ?ActiveCampaign $oConnect;

	public static function connect( string $apiKey, string $url ): ?ActiveCampaignConnection {
		if( self::$oSelf === null ) {
			self::$oSelf = new static();
		}

		self::$oConnect = new ActiveCampaign($url, $apiKey);
		return self::$oSelf;
	}

	public function ping(): bool {
		try {
			self::$oConnect->lists();

			return true;
		} catch( Exception $oException ) {
			return false;
		}
	}

	public function getLists() {
		$aLists = self::$oConnect->lists();
		if( empty($aLists) ) {
			return MessageFactory::factory()
				->error(
					esc_html__('Oops! Look like you dont\'t have any list yet. Please create one.',
						' myshopkit'),
					404)
				;
		}
		$aFilteredLists = array_map(function( $aLists ) {
			return [
				'id'    => $aLists->id,
				'label' => $aLists->name,
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

	private function findListInLists( string $listID, array $aLists ): array {
		if( !empty($aLists) ) {
			foreach( $aLists['data']['items'] as $aList ) {
				if( $aList['id'] == $listID ) {
					return $aList;
				}
			}
		}

		return [];
	}

	public function getListInfo( string $listID ) {
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

	public function addEmailToList( string $email, string $listID, string $name = '' ) {
		try {
			$contactID = self::$oConnect->createContact($email, $name, '')->id;
			self::$oConnect->updateListStatus($listID, $contactID, true);

			return MessageFactory::factory()
				->success(
					esc_html__('Hoorays! Your email has been saved successfully.',
						'myshopkit')
				);
		} catch( Exception $oException ) {
			return MessageFactory::factory()
				->error(
					esc_html__('Oops! This email address has already subscribed.',
						'myshopkit'),
					400
				);
		}
	}
}
