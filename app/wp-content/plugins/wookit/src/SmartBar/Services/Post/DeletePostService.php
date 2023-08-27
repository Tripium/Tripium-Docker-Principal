<?php


namespace WooKit\SmartBar\Services\Post;


use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Post\IDeleteUpdateService;
use WooKit\Shared\Post\TraitIsPostAuthor;
use WooKit\Shared\Post\TraitIsPostType;
use WP_Post;

class DeletePostService implements IDeleteUpdateService {
	use TraitIsPostAuthor;
	use TraitIsPostType;

	private $postID;

	public function setID( $id ): self {
		$this->postID = $id;

		return $this;
	}


	public function delete(): array {
		try {
			$this->isPostAuthor( $this->postID );
			$this->isPostType( $this->postID, AutoPrefix::namePrefix( 'smartbar' ) );
			$oPost = wp_delete_post( $this->postID, true );

			if ( $oPost instanceof WP_Post ) {
				return MessageFactory::factory()->success( esc_html__( 'Congrats, the smart bar has been deleted.',
					'wookit' ), [
					'id' => (string) $oPost->ID
				] );
			}

			return MessageFactory::factory()->error(
				esc_html__( 'Sorry, We could not delete this smart bar.', 'wookit' ), 400
			);
		}
		catch ( Exception $oException ) {
			return MessageFactory::factory()->error(
				$oException->getMessage(),
				$oException->getCode()
			);
		}

	}
}
