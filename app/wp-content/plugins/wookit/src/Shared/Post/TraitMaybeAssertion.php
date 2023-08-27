<?php


namespace WooKit\Shared\Post;


use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Assert;

trait TraitMaybeAssertion {
	/**
	 * @param $aField
	 * @param $value
	 *
	 * @return array
	 */
	protected function maybeAssert( $aField, $value ): array {
		if ( ! isset( $aField['assert'] ) ) {
			return MessageFactory::factory()->success( 'Passed' );
		}

		$aResponse = Assert::perform( $aField['assert'], $value );
		if ($aResponse['status'] == 'success') {
		    return $aResponse;
        }

		return MessageFactory::factory()->error(
		    sprintf(
                esc_html__('We got an issue on %s field. %s', 'wookit'),
                $aField['key'],
                $aResponse['message']
            ),
            $aResponse['code']
        );
	}
}
