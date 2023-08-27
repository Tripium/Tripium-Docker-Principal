<?php

namespace WooKit\MailServices\iContact\src\Middlewares;

use Exception;
use iContactApi;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\MailServices\iContact\src\Shared\IcontactConnection;

class IsValidAPIKeyMiddleware implements IMiddleware{

	public function validation( array $aAdditional = [] ): array {
		$appID       = $aAdditional['appID'] ?? '';
		$appUsername = $aAdditional['appUsername'] ?? '';
		$appPassword = $aAdditional['appPassword'] ?? '';
		if( !empty($appID) && !empty($appUsername) && !empty($appPassword) ) {
			if( IcontactConnection::connect($appID, $appUsername, $appPassword)->ping() ) {
				return MessageFactory::factory()->success('Oke');
			}
		}
		return MessageFactory::factory()
			->error(
				esc_html__('Invalid API Key',
					'myshopkit'),
				400
			);
	}
}
