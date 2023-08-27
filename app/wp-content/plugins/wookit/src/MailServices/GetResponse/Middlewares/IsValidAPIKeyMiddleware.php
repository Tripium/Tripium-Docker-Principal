<?php

namespace WooKit\MailServices\GetResponse\Middlewares;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\MailServices\GetResponse\Shared\GetResponseConnection;

class IsValidAPIKeyMiddleware implements IMiddleware{

	public function validation( array $aAdditional = [] ): array {
		$apiKey = $aAdditional['apiKey'] ?? '';
		if( !empty($apiKey) ) {
			if( GetResponseConnection::connect($apiKey)->ping() ) {
				return MessageFactory::factory()->success('OK');
			}
			return MessageFactory::factory()
			->error(
				esc_html__('Invalid API Key',
					'wookit'),
				400
			);
		}
		return MessageFactory::factory()
			->error(
				esc_html__('Invalid API Key',
					'wookit'),
				400
			);
	}
}
