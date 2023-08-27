<?php

namespace WooKit\MailServices\ActiveCampaign\Middlewares;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\MailServices\ActiveCampaign\Shared\ActiveCampaignConnection;

class IsValidAPIKeyMiddleware implements IMiddleware{

	public function validation( array $aAdditional = [] ): array {
		$apiKey = $aAdditional['apiKey'] ?? '';
		$url    = $aAdditional['url'] ?? '';

		if( ActiveCampaignConnection::connect($apiKey, $url)->ping() ) {
			return MessageFactory::factory()
				->success(
					esc_html__('OK',
						'myshopkit'),
					[
						'apiKey' => $apiKey,
						'url'    => $url,
					]
				);
		}
		return MessageFactory::factory()
			->error(
				esc_html__('Invalid API Key',
					'myshopkit'),
				400
			);
	}
}
