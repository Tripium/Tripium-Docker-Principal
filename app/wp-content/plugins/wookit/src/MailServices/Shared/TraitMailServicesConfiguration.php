<?php

namespace WooKit\MailServices\Shared;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;

trait TraitMailServicesConfiguration{
	/**
	 * @param string $field
	 * @param  $value
	 */
	protected function updateCampaignAPIKeysConfiguration( string $field, $value ) {

		$aUpdateInfo         = $this->getCurrentUserMeta(get_current_user_id());
		$aUpdateInfo[$field] = $value;
		update_user_meta(get_current_user_id(), AutoPrefix::namePrefix($this->key), $aUpdateInfo);
	}

	/**
	 * @param string $postID
	 * @param string $field
	 * @param string $value
	 */
	protected function updateCampaignListIDConfiguration( string $postID, string $field, string $value ) {
		$aUpdateInfo         = $this->getCurrentPostMeta($postID);
		$aUpdateInfo[$field] = $value;
		update_post_meta(intval($postID), AutoPrefix::namePrefix($this->key), ($aUpdateInfo));
	}

	/**
	 * @param $postID
	 *
	 * @return array
	 */
	protected function getCurrentPostMeta( $postID ): array {
		$aPostMeta = get_post_meta(intval($postID), AutoPrefix::namePrefix($this->key), true);

		$aPostMeta = is_array($aPostMeta) ? $aPostMeta : [];

		return wp_parse_args($aPostMeta, [
			'listID'    => '',
			'listLabel' => '',
			'status'    => 'deactive',
		]);
	}

	/**
	 * @param $userID
	 *
	 * @return array
	 */
	protected function getCurrentUserMeta( $userID ): array {
		$aUserMeta = get_user_meta($userID, AutoPrefix::namePrefix($this->key), true);
		$aUserMeta = is_array($aUserMeta) ? $aUserMeta : [];

		return wp_parse_args($aUserMeta, [
			'apiKey'   => '',
			'selected' => false,
		]);
	}

	public function saveServiceStatus( string $postID, ?string $status ): array {
		if( empty($status) || !in_array($status, ['deactive', 'active']) ) {
			return MessageFactory::factory()
				->error(esc_html__('Look like the value of your status field is invalid or empty. Please re-check it before process any further.',
					'myshopkit'), 400)
				;
		}
		$aResponseCheckIsUserLoggedIn = $this->checkIsUserLoggedIn();
		if( $aResponseCheckIsUserLoggedIn['status'] == 'success' ) {
			$this->updateCampaignListIDConfiguration($postID, 'status', $status);

			return MessageFactory::factory()->success(esc_html__('Your service status has been changed',
				'myshopkit'))
				;
		}

		return MessageFactory::factory()
			->error($aResponseCheckIsUserLoggedIn['message'], $aResponseCheckIsUserLoggedIn['code'])
			;
	}

	public function protectValue( string $value, int $trimAfter, string $encryptPart = "*****" ): string {
		return substr($value, 0, $trimAfter) . $encryptPart;
	}
}
