<?php

namespace WooKit\MailServices\Shared;

use WooKit\Illuminate\Message\MessageFactory;

trait TraitMailServicesValidation {
	use TraitMailServicesConfiguration;

	/**
	 * @param string|null $email
	 *
	 * @return array
	 */
	protected function checkIsEmailValid( ?string $email ): array {
		if ( ! empty( $email ) ) {
			if ( is_email( $email ) ) {
				return MessageFactory::factory()->success( 'OK' );
			}

			return MessageFactory::factory()->error( esc_html__( 'Oops! The email is invalid, please re-check it.',
				'myshopkit' ), 400 );
		}

		return MessageFactory::factory()
		                     ->error( esc_html__( 'Oops! The email field is empty, please re-check it.' ), 400 );
	}

	/**
	 * @return array
	 */

	protected function checkIsUserLoggedIn(): array {
		if ( is_user_logged_in() ) {
			return MessageFactory::factory()->success( 'OK' );
		}

		return MessageFactory::factory()->error( esc_html__( 'Please log in first.', 'myshopkit' ), 401 );
	}

	protected function checkIsCampaignIDValid( $campaignID ): array {
		if ( get_post_status( $campaignID ) ) {
			return MessageFactory::factory()->success( 'OK' );
		}

		return MessageFactory::factory()
		                     ->error( esc_html__( 'Oops! Look Like this campaign is invalid, please re-check it!',
			                     'myshopkit' ), 400 );
	}

}
