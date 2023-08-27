<?php


namespace MyshopKitDesignWizard\Share;



use MyshopKitDesignWizard\Shared\App;
use MyshopKitDesignWizard\Shared\Post;
use MyshopKitDesignWizard\Shared\Project\IPluckHandler;

abstract class PostSkeleton implements IPluckHandler {
	/**
	 * @var $postId
	 */
	protected $postId;

	/**
	 * @var string[]
	 */
	public $aPluck = [ 'id', 'label', 'thumbnails', 'thumbnail', 'content', 'taxonomies', 'color' ];

	protected string $tagTaxonomy     = 'post_tag';
	protected string $fakeTagTaxonomy = 'tags';

	/**
	 * @var array $aAdditionalArgs
	 */
	private array $aAdditionalArgs
		= [
			'imgSizes'                    => [ 'thumbnail', 'medium', 'large' ],
			'postCount'                   => [ 'postType' => '' ],
			'route'                       => '',
			'countChildrenWithPostStatus' => [ 'publish', 'pending', 'draft' ]
		];

	/**
	 * @var
	 */
	protected static $self;

	public abstract static function init(): IPluckHandler;

	/**
	 * @return string
	 */
	public function getEndpoint(): string {
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

	public function addAdditionalArgs( array $aArgs ): IPluckHandler {
		$this->aAdditionalArgs = wp_parse_args( $aArgs, $this->aAdditionalArgs );

		return $this;
	}

	public function setAdditionalArgs( array $aArgs ): IPluckHandler {
		$this->aAdditionalArgs = $aArgs;

		return $this;
	}

	public function getLabel( $postId = 0 ): ?string {
		return html_entity_decode( get_the_title( ! empty( $postId ) ? $postId : $this->postId ), ENT_HTML5 );
	}

	public function getId(): int {
		return $this->postId;
	}

	public function getFeaturedImageWidthHeight( $size = 'thumbnail' ): array {
		$featuredImgId = ! in_array( get_post_type( $this->postId ), [ 'attachment', 'inherit' ] ) ?
			get_post_thumbnail_id(
				$this->postId ) : $this->postId;
		if ( ! $featuredImgId ) {
			return [];
		}

		$aImageInfo = wp_get_attachment_image_src( $featuredImgId, $size );
		if ( empty( $aImageInfo ) ) {
			return [];
		}

		return [
			'width'  => $aImageInfo[1],
			'height' => $aImageInfo[2]
		];
	}

	public function getThumbnail( $size = 'large' ): ?array {
		$isRealImageId = in_array( get_post_type( $this->postId ), [ 'attachment', 'inherit' ] );
		if ( ! $isRealImageId ) {
			$thumbnailId = get_post_thumbnail_id( $this->postId );
			if ( empty( $thumbnailId ) ) {
				return [];
			}
		} else {
			$thumbnailId = $this->postId;
		}

		$url = in_array( get_post_type( $this->postId ), [ 'attachment', 'inherit' ] ) ? wp_get_attachment_image_url(
			$this->postId, $size ) : get_the_post_thumbnail_url( $this->postId, $size );

		return array_merge(
			$this->getFeaturedImageWidthHeight( $size ),
			[
				'id'  => $thumbnailId,
				'url' => $url
			]
		);
	}

	public function getThumbnails(): ?array {
		$aImgSizes = [];
		foreach ( $this->aAdditionalArgs['imgSizes'] as $size ) {
			$aImgSizes[] = $this->getThumbnail( $size );
		}

		return $aImgSizes;
	}

	public function getContent() {
		return do_shortcode( get_post_field( 'post_content', $this->postId ) );
	}

	public function getTotalChildren( $id = 0 ): int {
		$id       = empty( $id ) ? $this->postId : $id;
		$postType = ! empty( $this->aAdditionalArgs['postCount']['postType'] ) ?
			$this->aAdditionalArgs['postCount']['postType'] : get_post_type( $id );

		return (int) Post::countChildren( $id, $postType, $this->aAdditionalArgs['countChildrenWithPostStatus'] );
	}

	public function getTemplateCategories(): array {
		$aRawCategories = wp_get_post_terms(
			$this->postId,
			plGetConfig( 'taxonomies' )->getParam( 'taxonomies', true )
			                           ->getParam( 'pl_template_category', true )
			                           ->getParam( 'taxonomy' )
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
		return [
			plGetConfig( 'taxonomies' )->getParam( 'taxonomies', true )
			                           ->getParam( 'pl_template_category', true )
			                           ->getParam( 'taxonomy' ) => $this->getTemplateCategories()
		];
	}

	public function getParentInfo(): ?array {
		$parentPostId = (int) wp_get_post_parent_id( $this->postId );
		if ( empty( $parentPostId ) ) {
			return null;
		}

		return [
			'totalChildren' => $this->getTotalChildren( $parentPostId ),
			'label'         => $this->getLabel( $parentPostId ),
			'id'            => $parentPostId
		];
	}

	public function get(): array {
		$aData = [];
		foreach ( $this->aPluck as $pluck ) {
			$method = 'get' . ucfirst( $pluck );

			if ( method_exists( $this, $method ) ) {
				$aData[ $pluck ] = $this->{$method}();
			}
		}

		return $aData;
	}
}
