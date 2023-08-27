<?php

namespace WooKit\MailServices\Klaviyo\Middlewares;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\MailServices\Klaviyo\Shared\KlaviyoConnection;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\MailServices\Klaviyo\Controllers\KlaviyoController;
use WooKit\MailServices\Shared\TraitMailServicesConfiguration;

class IsValidListIDMiddleware implements IMiddleware {

	use TraitMailServicesConfiguration;

	public string $key         = 'klaviyo_info';
	public string $mailService = 'klaviyo';

	public function validation( array $aAdditional = [] ): array {
		$listID        = $aAdditional['listID'] ?? '';
		$privateApiKey = $aAdditional['privateApiKey'] ?? '';
		$publicApiKey  = $aAdditional['publicApiKey'] ?? '';


		if ( ! empty( $listID ) ) {
			return MessageFactory::factory()->response(
				KlaviyoConnection::connect( $privateApiKey, $publicApiKey )->getListInfo( $listID )
			);
		}

		return MessageFactory::factory()
		                     ->error( esc_html__( 'Oops! Look like you hasn\'t insert your list ID yet. Please re-check it.',
			                     'myshopkit' ),
			                     400 );
	}
}
