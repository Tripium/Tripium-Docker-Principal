<?php


namespace WooKit\MailServices\MailChimp\Middleware;


use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;

class IsMailChimpActivateMiddleware implements IMiddleware {
	public function validation( array $aAdditional = [] ): array {
		if ( ! isset( $aAdditional['postID'] ) || empty( $aAdditional['postID'] ) ) {
			return MessageFactory::factory()->error( 'The campaign is disabled', 403 );
		}

		$aPostMeta = get_post_meta( $aAdditional['postID'], AutoPrefix::namePrefix( 'mailchimp_info' ), true );

		if ( isset( $aPostMeta['status'] ) && $aPostMeta['status'] == 'active' ) {
			return MessageFactory::factory()->success( 'Passed' );
		}

		return MessageFactory::factory()->error( 'The campaign is disabled', 403 );
	}
}
