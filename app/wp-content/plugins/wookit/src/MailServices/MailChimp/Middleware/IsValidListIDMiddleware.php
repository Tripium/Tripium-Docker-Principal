<?php


namespace WooKit\MailServices\MailChimp\Middleware;


use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\MailChimp\Shared\MailChimpConnection;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\Shared\Middleware\Middlewares\TraitLocale;
use WooKit\Shared\MultiLanguage\MultiLanguage;

class IsValidListIDMiddleware implements IMiddleware {
	use TraitLocale;

	/**
	 * @throws \Exception
	 */
	public function validation( array $aAdditional = [] ): array {
		if ( ! isset( $aAdditional['listID'] ) || empty( $aAdditional['listID'] ) ) {
			return MessageFactory::factory()->error( MultiLanguage::setLanguage( $this->getMiddlewareLocale(
				$aAdditional ) )->getMessage( 'listIDIsRequired' ), 400 );
		}

		$aLists = MailChimpConnection::connect( $aAdditional['apiKey'] )->getLists();

		foreach ( $aLists as $aListItem ) {
			if ( $aListItem['id'] == $aAdditional['listID'] ) {
				return MessageFactory::factory()->success( 'Passed' );
			}
		}

		return MessageFactory::factory()->error( MultiLanguage::setLanguage( $this->getMiddlewareLocale(
			$aAdditional ) )->getMessage( 'invalidListID' ), 400 );
	}
}
