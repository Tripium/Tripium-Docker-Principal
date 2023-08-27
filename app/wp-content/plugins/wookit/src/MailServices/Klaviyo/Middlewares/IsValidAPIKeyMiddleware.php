<?php

namespace WooKit\MailServices\Klaviyo\Middlewares;

use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use Klaviyo\Exception\KlaviyoException;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Klaviyo\Shared\KlaviyoConnection;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;

class IsValidAPIKeyMiddleware implements IMiddleware {
	use TraitMailServicesConfiguration;

	public string $key         = 'klaviyo_info';
	public string $mailService = 'klaviyo';

	public function validation( array $aAdditional = [] ): array {
		$privateApiKey = $aAdditional['privateApiKey'] ?? '';
		$publicApiKey  = $aAdditional['publicApiKey'] ?? '';
		if ( ! empty( $privateApiKey ) && ! empty( $publicApiKey ) ) {
			if ( KlaviyoConnection::connect( $privateApiKey, $publicApiKey )->ping() ) {
				return MessageFactory::factory()->success( 'Oke' );
			}

			return MessageFactory::factory()
			                     ->error(
				                     esc_html__( 'Invalid API Key',
					                     'myshopkit' ),
				                     400
			                     );
		}

		return MessageFactory::factory()
		                     ->error( esc_html__( 'Invalid API Key',
			                     'myshopkit' ), 400 );
	}
}
