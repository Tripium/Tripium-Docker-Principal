<?php


namespace MyshopKitDesignWizard\Shared\Post;


use MyShopKit\Shared\AutoPrefix;

class PostHelper {
	public static function getPostTypes(): array {
		return [ AutoPrefix::namePrefix( 'popup' ), AutoPrefix::namePrefix( 'smartbar' ) ];
	}

	public static function postTypeSupported( string $postType ): bool {
		return in_array( AutoPrefix::namePrefix( $postType ), self::getPostTypes() );
	}
}
