<?php

namespace MyshopKitDesignWizard\Illuminate\Prefix;

class AutoPrefix {
	public static function namePrefix( $name ) {
		return strpos( $name, MYSHOPKIT_DW_PREFIX ) === 0 ? $name : MYSHOPKIT_DW_PREFIX . $name;
	}

	public static function removePrefix( string $name ): string {
		if ( strpos( $name, MYSHOPKIT_DW_PREFIX ) === 0 ) {
			$name = str_replace( MYSHOPKIT_DW_PREFIX, '', $name );
		}

		return $name;
	}
}
