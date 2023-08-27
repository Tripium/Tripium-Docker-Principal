<?php

namespace WooKit\MailServices\ActiveCampaign\Middlewares;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\MailServices\ActiveCampaign\Shared\ActiveCampaignConnection;

class IsValidListIDMiddleware implements IMiddleware{

	public function validation( array $aAdditional = [] ): array {
		$apiKey    = $aAdditional['apiKey'] ?? '';
		$url       = $aAdditional['url'] ?? '';
		$listID    = $aAdditional['listID'] ?? '';
		$aResponse = ActiveCampaignConnection::connect($apiKey, $url)->getListInfo($listID);
		return MessageFactory::factory()->response($aResponse);
	}
}
