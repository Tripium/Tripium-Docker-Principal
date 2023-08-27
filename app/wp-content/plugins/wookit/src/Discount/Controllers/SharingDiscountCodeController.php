<?php


namespace WooKit\Discount\Controllers;


use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\MultiLanguage\MultiLanguage;

class SharingDiscountCodeController {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerRouters' ] );
		add_filter( WOOKIT_PREFIX . 'Shared/MultiLanguage/Config/Languages/English',
			[ $this, 'addLanguage' ], 10, 2 );
		add_filter( WOOKIT_PREFIX . 'Shared/MultiLanguage/Config/Languages/Vietnamese',
			[ $this, 'addLanguage' ], 10, 2 );
	}

	public function addLanguage( $aLanguage, $language ) {
		$languagePath = dirname( plugin_dir_path( __FILE__ ) ) . '/Configs/Languages/' . $language . '.php';
		if ( is_file( $languagePath ) ) {
			$aLanguage = array_merge( $aLanguage, include $languagePath );
		}

		return $aLanguage;
	}

	public function registerRouters() {
		register_rest_route( WOOKIT_REST, 'coupons/giveaway',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'sharingCouponCode' ],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	private function findCouponCodeByCouponID( $couponID, array $aSettings ): string {
		foreach ( $aSettings as $aSetting ) {
			if ( ! isset( $aSetting['coupon'] ) || empty( $aSetting['coupon'] ) ) {
				continue;
			}

			if ( $aSetting['coupon']['id'] == $couponID ) {
				return $aSetting['coupon']['code'];
			}
		}

		return '';
	}

	public function sharingCouponCode( \WP_REST_Request $oRequest ) {
		$postID = $oRequest->get_param( 'id' );
		if ( empty( $postID ) ) {
			return MessageFactory::factory( 'rest' )->error(
				MultiLanguage::setLanguage( $oRequest->get_param( 'locale' ) )->getMessage( 'idIsRequired' ),
				400
			);
		}

		if ( get_post_status( $postID ) !== 'publish' ) {
			return MessageFactory::factory( 'rest' )->error(
				MultiLanguage::setLanguage( $oRequest->get_param( 'locale' ) )
				             ->getMessage( 'campaignIsNoLongerAvailable' ),
				404
			);
		}

		$aConfig = get_post_meta( $oRequest->get_param( 'id' ), AutoPrefix::namePrefix( 'config' ), true );
		if ( empty( $aConfig ) ) {
			return MessageFactory::factory( 'rest' )->error(
				MultiLanguage::setLanguage( $oRequest->get_param( 'locale' ) )->getMessage( 'missingConfig' ),
				404
			);
		}

		$goal = $aConfig['goal'];
		if ( strpos( $goal, 'wheel' ) !== false ) {
			$couponID = $oRequest->get_param( 'couponID' );
			if ( empty( $couponID ) ) {
				return MessageFactory::factory( 'rest' )->error(
					MultiLanguage::setLanguage( $oRequest->get_param( 'locale' ) )->getMessage( 'couponIDIsRequired' ),
					404
				);
			}

			if ( ! isset( $aConfig['wheelSettings'] ) || ! isset( $aConfig['wheelSettings']['settings'] ) ) {
				return MessageFactory::factory( 'rest' )->error(
					MultiLanguage::setLanguage( $oRequest->get_param( 'locale' ) )
					             ->getMessage( 'missingWheelSettings' ),
					404
				);
			}

			$couponCode = $this->findCouponCodeByCouponID( $couponID, $aConfig['wheelSettings']['settings'] );
			if ( empty( $couponCode ) ) {
				return MessageFactory::factory( 'rest' )->error(
					esc_html__('Sorry, We could not find Coupon Code in this campaign.', 'wookit'),
					404
				);
			}

			return MessageFactory::factory( 'rest' )->success(
				'We found the Coupon Code',
				[
					'couponCode' => $couponCode
				]
			);
		}

		if ( ! isset( $aConfig['settings'] ) || ! isset( $aConfig['settings']['coupon'] ) || ! isset( $aConfig['settings']['coupon']['code'] ) ) {
			return MessageFactory::factory( 'rest' )->error(
				esc_html__('Sorry, We could not find Coupon Code in this campaign.', 'wookit'),
				404
			);
		}

		return MessageFactory::factory( 'rest' )->success(
			'We found the Coupon Code',
			[
				'couponCode' => $aConfig['settings']['coupon']['code']
			]
		);
	}
}
