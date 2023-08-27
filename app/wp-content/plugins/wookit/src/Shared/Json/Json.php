<?php


namespace WooKit\Shared\Json;


class Json {
	public static function encode( array $aData ) {
		return json_encode( $aData, JSON_UNESCAPED_UNICODE );
	}

	public static function decode( string $data ): array {
		$aData = json_decode( $data, true );
		if ( empty( $aData ) ) {
			$aData = json_decode( stripslashes( $data ), true );
		}

		return !empty( $aData ) ? $aData : [];
	}
}
