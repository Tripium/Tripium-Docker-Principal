<?php

namespace WooKit\MailServices\CampaignMonitor\Middlewares;

use CS_REST_Clients;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;

class IsValidAPIKeyMiddleware implements IMiddleware{

	public function validation( array $aAdditional = [] ): array {
		$apiKey = $aAdditional['apiKey'];
		$clientID = $aAdditional['clientID'];
		if( !empty($apiKey) && !empty($clientID) ) {
			$auth = new CS_REST_Clients($clientID, $apiKey);
			if( $auth->get_lists()->http_status_code == 200 ) {
				return MessageFactory::factory()->success('OK',
					[
						'apiKey'   => $apiKey,
						'clientID' => $clientID,
					]
				);
			}

			return MessageFactory::factory()
				->error(esc_html__('Invalid API Key.',
					'wookit'), 400)
				;
		}

		return MessageFactory::factory()
			->error(esc_html__('Invalid API Key.',
				'wookit'), 400);
	}
}
