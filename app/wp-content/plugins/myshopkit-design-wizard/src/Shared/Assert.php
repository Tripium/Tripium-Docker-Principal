<?php


namespace MyshopKitDesignWizard\Shared;




use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;

class Assert {
	/**
	 * @throws \Exception
	 */
	public static function isJson( string $value ): bool {
		json_decode( $value );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return true;
		}

		throw new \Exception( esc_html__( 'The data is not json format', 'myshopkit-design-wizard' ) );
	}

	public static function perform( $aAssert, $value ): array {
		try {
			$func = $aAssert['callbackFunc'];
			$msg  = $aAssert['message'] ?? '';

			switch ( $func ) {
				case 'notEmpty':
				case 'isEmpty':
				case 'true':
				case 'false':
				case 'notFalse':
				case 'null':
					call_user_func( [ '\Webmozart\Assert\Assert', $func ], $value, $msg );
					break;
				case 'inArray':
				case 'eq':
				case 'same':
				case 'notEq':
				case 'greaterThan':
				case 'greaterThanEq':
				case 'lessThan':
				case 'lessThanEq':
					$compare = $aAssert['expected'];
					if ($func == "inArray") {
						if (!property_exists('\Webmozart\Assert\Assert', $func)) {
							$func = 'oneOf';
						}
					}
					call_user_func( [ '\Webmozart\Assert\Assert', $func ], $value, $compare, $msg );
					break;
				case 'isJson':
					self::isJson( $value );
					break;
			}

			return MessageFactory::factory()->success( esc_html__( 'The data has been validated and it\'s correct.',
				'myshopkit-design-wizard' ) );
		}
		catch ( \Exception $oException ) {
			return MessageFactory::factory()->error( $oException->getMessage(), $oException->getCode() );
		}
	}
}
