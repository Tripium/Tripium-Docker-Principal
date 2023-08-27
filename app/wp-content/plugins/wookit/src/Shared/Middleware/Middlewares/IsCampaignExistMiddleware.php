<?php


namespace WooKit\Shared\Middleware\Middlewares;


use Exception;
use WooKit\Illuminate\Message\MessageFactory;

class IsCampaignExistMiddleware implements IMiddleware {
	protected array $aStatusCampaign = [ 'publish', 'draft' ];

	/**
	 * @throws Exception
	 */
	public function validation( array $aAdditional = [] ): array {
		$postID = $aAdditional['postID'] ?? '';
		if ( empty( $postID ) ) {
			throw new Exception( 'Sorry, the campaign is required', 400 );
		}
		if ( ! in_array( get_post_status( $postID ), $this->aStatusCampaign ) ) {
			throw new Exception( 'Sorry, the post doest not exist at the moment', 400 );
		}

		return MessageFactory::factory()->success( 'Passed', true );
	}
}
