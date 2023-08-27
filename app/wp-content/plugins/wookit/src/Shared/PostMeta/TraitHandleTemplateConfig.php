<?php


namespace WooKit\Shared\PostMeta;


use Exception;
use WooKit\Shared\App;
use WooKit\Shared\AutoPrefix;

trait TraitHandleTemplateConfig {

	/**
	 * @throws Exception
	 */
	public function handleTemplates( $aRawTemplate, string $postID ): bool {
		$metaKey = AutoPrefix::namePrefix( 'showOnPage' );
		if ( ! empty( $aRawTemplate ) && is_array( $aRawTemplate ) ) {
			update_post_meta( $postID, AutoPrefix::namePrefix( 'showOnPageMode' ), 'specified_pages' );
			delete_post_meta( $postID, $metaKey );
			foreach ( $aRawTemplate as $template ) {
				add_post_meta( $postID, $metaKey, $template );
			}
		} else {
			update_post_meta( $postID, AutoPrefix::namePrefix( 'showOnPageMode' ), 'all' );
			update_post_meta( $postID, $metaKey, 'all' );
		}

		return true;
	}

	/**
	 * @throws Exception
	 */
	public function checkTemplateExist( $template ): bool {
		if ( array_key_exists( $template, $this->defineTemplatesConfigs() ) ) {
			return true;
		} else {
			throw new Exception( sprintf( esc_html__( "Sorry,the template %s does not exist", 'wookit' ),
				$template ),
				401 );
		}
	}

	public function defineTemplatesConfigs(): array {
		return App::get( 'TemplateMeta' );
	}

	/**
	 * @throws Exception
	 */
	public function coverRawTemplate( array $aRawDataTemplate ): array {
		$aData = [];
		if ( ! empty( $aRawDataTemplate ) ) {
			foreach ( $aRawDataTemplate as $rawTemplate ) {
				if ( array_key_exists( $rawTemplate, $this->defineConfigCoverTemplate() ) ) {
					$aData[] = $rawTemplate;
				} elseif ( preg_match( "/^pages\/[^\/]+/", $rawTemplate ) ) {
					$aData[] = $rawTemplate;
				}
			}
		}

		return $aData;
	}

	public function defineConfigCoverTemplate(): array {
		return [
			'/'            => 'template-index',
			'/blogs'       => 'template-blog',
			'/cart'        => 'template-cart',
			'/collections' => 'template-list-collections',
			'/pages'       => 'template-page',
			'/products'    => 'template-product',
			'/search'      => 'template-search',
		];
	}
}
