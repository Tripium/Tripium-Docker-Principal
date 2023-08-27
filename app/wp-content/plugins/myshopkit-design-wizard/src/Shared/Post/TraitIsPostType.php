<?php


namespace MyshopKitDesignWizard\Shared\Post;


trait TraitIsPostType {

	/**
	 * @throws \Exception
	 */
	public function isPostType( $id, $postType ): bool {
		if ( get_post_field( 'post_type', $id ) != $postType ) {
			throw new \Exception( sprintf( esc_html__( 'Unfortunately, this item is not a %s',
				MYSHOPKIT_MB_REST_NAMESPACE ), $postType ) );
		}

		return true;
	}
}
