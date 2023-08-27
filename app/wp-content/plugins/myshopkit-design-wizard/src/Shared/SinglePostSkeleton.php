<?php

namespace MyshopKitDesignWizard\Share;

use Proomoland\Project\Models\ProjectModel;
use MyshopKitDesignWizard\Shared\Project\IPluckHandler;

class SinglePostSkeleton extends PostSkeleton {
	/**
	 * @return IPluckHandler
	 */
	public static function init(): IPluckHandler {
		if ( self::$self ) {
			return self::$self;
		}

		self::$self = new self();

		return self::$self;
	}

	/**
	 * @var $aAdditionalArgs
	 */
	private $aAdditionalArgs
		= [
			'imgSizes'  => [ 'thumbnail', 'medium', 'large', 'full' ],
			'postCount' => [ 'postType' => '' ],
			'route'     => ''
		];

	public function getPostTaxonomies(): array {
		if ( isset( $this->aAdditionalArgs['taxonomies'] ) &&
		     ! empty( $this->addAdditionalArgs( [ 'taxonomies' ] ) ) ) {
			$aTaxonomies = $this->aAdditionalArgs['taxonomies'];
		} else {
			$aTaxonomies = plGetConfig( 'taxonomies' )->getParam( 'taxonomies', true )
			                                          ->getParam( 'pl_template_category', true )
			                                          ->getParam( 'taxonomy' );
		}

		$aTaxonomies = is_array( $aTaxonomies ) ? $aTaxonomies : [ $aTaxonomies ];

		if ( array_search( $this->fakeTagTaxonomy, $aTaxonomies ) === false ) {
			$aTaxonomies[] = $this->fakeTagTaxonomy;
		}

		return $aTaxonomies;
	}

	public function getEndpoint(): string {
		if ( empty( $this->aAdditionalArgs['route'] ) ) {
			$this->aAdditionalArgs['route'] = Route::getEndpoint( get_post_type( $this->postId ) );
		}

		return sprintf( rtrim( $this->aAdditionalArgs['route'], '/' ), $this->postId );
	}

	public function setId( int $id ): IPluckHandler {
		$this->postId = $id;

		return $this;
	}

	public function setPluck( $pluck ): IPluckHandler {
		if ( is_array( $pluck ) ) {
			$this->aPluck = $pluck;
		} else {
			$this->aPluck = explode( ',', $pluck );
			$this->aPluck = array_map( function ( $pluck ) {
				return trim( $pluck );
			}, $this->aPluck );
		}

		return $this;
	}

	public function getLabel( $postId = 0 ): ?string {
		return html_entity_decode( get_the_title( ! empty( $postId ) ? $postId : $this->postId ), ENT_HTML5 );
	}

	public function getId(): int {
		return $this->postId;
	}

	public function getThumbnails(): ?array {
		$aImgSizes = [];

		foreach ( $this->aAdditionalArgs['imgSizes'] as $size ) {
			$aImgSizes[ $size ] = $this->getThumbnail( $size );
		}

		$aImgSizes['5x5'] = $this->getThumbnail( AutoPrefix::addPrefix( 'image-size-5x5' ) );

		return $aImgSizes;
	}

	public function getContent() {
		$fileId = get_post_meta( $this->postId, AutoPrefix::addPrefix( 'code_id' ), true );
		if ( $fileId ) {
			$url = wp_get_attachment_url( $fileId );
			if ( $url ) {
				$content = file_get_contents( $url );
			}
		}

		if ( ! isset( $content ) || empty( $content ) ) {
			$content = get_post_field( 'post_content', $this->postId );
		}

		return json_decode( base64_decode( Post::filterContent( $content ) ), true );
	}

	public function getTemplateCategories( $taxonomy = '' ): array {
		if ( empty( $taxonomy ) ) {
			$taxonomy = plGetConfig( 'taxonomies' )->getParam( 'taxonomies', true )
			                                       ->getParam( 'pl_template_category', true )
			                                       ->getParam( 'taxonomy' );
		}

		if ( $taxonomy == $this->fakeTagTaxonomy ) {
			$taxonomy = $this->tagTaxonomy;
		}

		$aRawCategories = wp_get_post_terms(
			$this->postId,
			$taxonomy
		);

		if ( empty( $aRawCategories ) || is_wp_error( $aRawCategories ) ) {
			return [];
		}

		$aCategories = [];

		App::get( 'TermSkeleton' )->setPluck( [ 'id', 'label', 'endpoint' ] );
		foreach ( $aRawCategories as $oCategory ) {
			$aCategories[] = App::get( 'TermSkeleton' )->setId( abs( $oCategory->term_id ) )->get();
		}

		return $aCategories;
	}

	public function getTaxonomies(): array {
		$aTaxonomies = [];
		foreach ( $this->getPostTaxonomies() as $taxonomy ) {
			$aTaxonomies[ $taxonomy ] = $this->getTemplateCategories( $taxonomy );
		}

		return $aTaxonomies;
	}

	public function getColor(): string {
		$color = get_post_meta( $this->postId, Prefix::addPrefix( 'color' ), true );

		return empty( $color ) ? '' : $color;
	}

	public function getTotalChildrenText( $id = 0 ): string {
		$total = self::getTotalChildren();
		if ( ! $total ) {
			return esc_html__( '0 template', 'promoland' );
		}

		return sprintf( _n( '%s template', '%s templates', $total, 'promoland' ), number_format_i18n( $total ) );
	}

	public function getIsGlobalTemplate(): bool {
		return ! empty( ProjectModel::isGlobalTemplate( $this->postId ) );
	}
}
