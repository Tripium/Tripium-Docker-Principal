<?php

namespace WooKit\MailServices\MailChimp\Middleware;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\MailChimp\Shared\MailChimpConnection;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\Shared\Middleware\Middlewares\TraitLocale;
use WooKit\Shared\MultiLanguage\MultiLanguage;

class IsValidAPIKeyMiddleware implements IMiddleware {
	use TraitLocale;

	/**
	 * @throws \Exception
	 */
	public function validation( array $aAdditional = [] ): array {
		if ( ! isset( $aAdditional['apiKey'] ) || empty( $aAdditional['apiKey'] ) ) {
			return MessageFactory::factory()->error( MultiLanguage::setLanguage( $this->getMiddlewareLocale(
				$aAdditional ) )->getMessage( 'invalidAPIKey' ), 400 );
		}

		if ( MailChimpConnection::connect( $aAdditional['apiKey'] )->ping() ) {
			return MessageFactory::factory()->success( 'Passed' );
		}

		return MessageFactory::factory()->error( MultiLanguage::setLanguage( $this->getMiddlewareLocale(
			$aAdditional ) )->getMessage( 'invalidAPIKey' ), 400 );
	}
}
