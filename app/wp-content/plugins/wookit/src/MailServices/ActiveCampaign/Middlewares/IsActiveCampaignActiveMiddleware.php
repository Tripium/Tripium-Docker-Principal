<?php

namespace WooKit\MailServices\ActiveCampaign\Middlewares;

use WooKit\Shared\AutoPrefix;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;

class IsActiveCampaignActiveMiddleware implements IMiddleware{

	public string $key = 'active_campaign_info';
	public string $mailService = 'activecampaign';

	public function validation( array $aAdditional = [] ): array {
		if( !isset($aAdditional['postID']) || empty($aAdditional['postID']) ) {
			return MessageFactory::factory()->error('The campaign is disabled', 403);
		}

		$aPostMeta = get_post_meta($aAdditional['postID'], AutoPrefix::namePrefix($this->key), true);

		if( isset($aPostMeta['status']) && $aPostMeta['status'] == 'active' ) {
			return MessageFactory::factory()->success('Passed');
		}

		return MessageFactory::factory()->error('The campaign is disabled', 403);
	}

}
