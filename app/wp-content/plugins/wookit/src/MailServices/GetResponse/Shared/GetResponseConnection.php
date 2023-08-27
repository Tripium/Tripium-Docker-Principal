<?php

namespace WooKit\MailServices\GetResponse\Shared;

use phpDocumentor\Reflection\Types\False_;
use Getresponse\Sdk\Client\Exception\MalformedResponseDataException;
use Getresponse\Sdk\Client\GetresponseClient;
use Getresponse\Sdk\GetresponseClientFactory;
use Getresponse\Sdk\Operation\Campaigns\GetCampaigns\GetCampaigns;
use Getresponse\Sdk\Operation\Contacts\CreateContact\CreateContact;
use Getresponse\Sdk\Operation\Model\CampaignReference;
use Getresponse\Sdk\Operation\Model\NewContact;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;
use WooKit\MailServices\Shared\TraitMailServicesValidation;

class GetResponseConnection{
	private static ?GetResponseConnection $oSelf = NULL;
	private static ?GetresponseClient $oConnect;

	public static function connect( string $apiKey ): GetResponseConnection {
		if( self::$oSelf === NULL ) {
			self::$oSelf = new static();
		}
		self::$oConnect = GetresponseClientFactory::createWithApiKey($apiKey);
		return self::$oSelf;
	}

	public function ping(): bool {
		$pingStatus = self::$oConnect->call(new GetCampaigns());
		if( $pingStatus->isSuccess() ) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @throws MalformedResponseDataException
	 */
	public function getLists(): array {
		$aLists        = self::$oConnect->call(new GetCampaigns())->getData();
		$aFilteredList = array_map(function( $aLists ) {
			return [
				'label' => $aLists['name'],
				'id'    => $aLists['campaignId'],
			];
		}, $aLists);
		return $aFilteredList;
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

	public function addEmailToList( $email, $listID ): array {
		$oResponse = self::$oConnect->call(
			new CreateContact(
				new NewContact(
					new CampaignReference($listID),
					$email
				)
			)
		);
		if( !$oResponse->isSuccess() ) {
			switch( $oResponse->getData()['httpStatus'] ) {
				case '400':
					$oResponse = MessageFactory::factory()
						->error(esc_html__('Look like your email doesn\'t meet Get Response policy.',
							'wookit'), 400);
					break;
				case '409' :
					$oResponse = MessageFactory::factory()
						->success(esc_html__('Hoorays! Your email has been saved to this list already.',
							'wookit'));
					break;

				default :
					$oResponse = MessageFactory::factory()
						->error(esc_html__('Look like there are some problems when we processing. Please check again.',
							'wookit'),
							400);
			}

			return $oResponse;
		}

		return MessageFactory::factory()
			->success(esc_html__('Hoorays! Your email has been save successfully.', 'wookit'))
			;
	}
}
