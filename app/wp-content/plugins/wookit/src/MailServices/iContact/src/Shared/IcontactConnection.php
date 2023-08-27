<?php

namespace WooKit\MailServices\iContact\src\Shared;

use Exception;
use iContactApi;
use WooKit\Illuminate\Message\MessageFactory;

class IcontactConnection{
	private static ?IcontactConnection $oSelf = null;
	private static ?iContactApi $oConnect;

	/**
	 * @param string $appID
	 * @param string $appUsername
	 * @param string $appPassword
	 * @return IcontactConnection|null
	 */
	public static function connect( string $appID, string $appUsername, string $appPassword ): ?IcontactConnection {
		if( self::$oSelf === null ) {
			self::$oSelf = new static();
		}

		self::$oConnect = iContactApi::getInstance()->setConfig([
				'appId'       => $appID,
				'apiUsername' => $appUsername,
				'apiPassword' => $appPassword,
			]
		)
		;
		return self::$oSelf;
	}

	/**
	 * @return bool
	 */
	public function ping(): bool {
		try {
			self::$oConnect->getContacts();

			return true;
		} catch( Exception $oException ) {
			return false;
		}
	}

	private function findListInLists( $listID, $aLists ): array {
		if( !empty($aLists) ) {
			foreach( $aLists as $aList ) {
				if( $aList['id'] == $listID ) {
					return $aList;
				}
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

	public function getLists(): array {
		try {
			$aListData = self::$oConnect->getLists();
			$aLists    = [];
			foreach( $aListData as $aList ) {
				$aLists[] = [
					'id'    => $aList->listId,
					'label' => $aList->name,
				];
			}
			return $aLists;
		} catch( Exception $oException ) {
			return [];
		}
	}

	public function addEmailToList( string $email, string $listID, string $name = '' ): array {
		try {
			$oContactInfo = self::$oConnect->addContact($email, null, null, $name);
			self::$oConnect->subscribeContactToList($oContactInfo->contactId, $listID);
			return MessageFactory::factory()
				->success(
					esc_html__('Hoorays! Your email has been saved successfully.',
					'wookit')
			);
		} catch( Exception $oException ) {
			return MessageFactory::factory()
				->error(
					esc_html__('Oops! Look like we meet a problem. Please re-check it.',
				'wookit'),
				400
			);
		}
	}
}
