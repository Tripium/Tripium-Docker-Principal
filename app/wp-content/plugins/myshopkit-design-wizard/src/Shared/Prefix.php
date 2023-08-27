<?php


namespace MyshopKitDesignWizard\Shared;


class Prefix {
	public static function addPrefix( $name ) {
		if ( strpos( $name, 'pl_' ) === false ) {
			$name = 'pl_' . $name;
		}

		return $name;
	}

	public static function removePrefix( $name ) {
		if ( strpos( $name, 'pl_' ) === 0 ) {
			$name = str_replace( 'pl_', '', $name );
		}

		return $name;
	}
}
