<?php


namespace Proomoland\Helpers;


class Route {
	public static function getEndpoint( $postType ) {
		$aPostType = proomolandRepository()->setFile( 'post_types' )->get( 'post_types', true )
		                                   ->get( $postType );

		if ( empty( $aPostType ) ) {
			return '';
		}

		return isset( $aPostType['route'] ) ? $aPostType['route'] : '';
	}
}
