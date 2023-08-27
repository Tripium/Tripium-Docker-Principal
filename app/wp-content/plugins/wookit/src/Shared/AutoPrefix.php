<?php

namespace WooKit\Shared;

class AutoPrefix {
	public static function namePrefix( $name ) {
		return strpos( $name, WOOKIT_PREFIX ) === 0 ? $name : WOOKIT_PREFIX . $name;
	}

	public static function removePrefix( string $name ): string {
		if ( strpos( $name, WOOKIT_PREFIX ) === 0 ) {
			$name = str_replace( WOOKIT_PREFIX, '', $name );
		}

		return $name;
	}
}
