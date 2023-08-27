<?php


namespace WooKit\Shared\Post;


trait TraitIsPostType {

	/**
	 * @throws \Exception
	 */
	public function isPostType( $id, $postType ): bool {
		if ( get_post_field( 'post_type', $id ) != $postType ) {
			throw new \Exception( sprintf( esc_html__( 'Unfortunately, this item is not a %s',
				'wookit' ), $postType ) );
		}

		return true;
	}
}
