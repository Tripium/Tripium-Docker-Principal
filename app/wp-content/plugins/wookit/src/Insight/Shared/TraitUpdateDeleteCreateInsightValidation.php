<?php


namespace WooKit\Insight\Shared;


use WooKit\Illuminate\Message\MessageFactory;

trait TraitUpdateDeleteCreateInsightValidation {
	/**
	 * Validate data trước khi thực hiện việc update hoặc create thống kê dữ liệu
	 *
	 * @param $postID
	 * @param bool $isRequiredToken
	 * @param string $expectedPostType
	 *
	 * @return array
	 */
	private function validateCreateOrUpdateInsight( $postID, bool $isRequiredToken = true, string $expectedPostType = ''
	): array {

		if ($isRequiredToken) {
			if (!is_user_logged_in()) {
				return MessageFactory::factory()->error(
					esc_html__( 'Sorry, You do not have permission this perform this action.', 'wookit' ), 404 );
			}
		}

		if ( get_post_status( $postID ) !== 'publish' ) {
			return MessageFactory::factory()->error(
				esc_html__( 'Sorry, the post doest not exist at the moment', 'wookit' ),
				404
			);
		}

		if ( ! empty( $expectedPostType ) && get_post_type( $postID ) !== $expectedPostType ) {
			return MessageFactory::factory()->error(
				esc_html__( 'Sorry, the item is no longer available', 'wookit' ),
				400
			);
		}

		return MessageFactory::factory()->success(
			esc_html__( 'The data has been validated', 'wookit' ),
			[
				'postID' => $postID
			]
		);
	}
}
