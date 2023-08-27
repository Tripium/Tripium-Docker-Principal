<?php


namespace MyshopKitDesignWizard\Shared\Project;


trait TraitConfiguration {
	private function getTaxonomyKeysByPostType( $postType ): array {
		$aTaxonomiesConfigurations = proomolandRepository()->setFile( 'taxonomies' )->get( 'taxonomies' );
		$aTaxonomies               = [];

		foreach ( $aTaxonomiesConfigurations as $aTaxonomy ) {
			if ( in_array( $postType, $aTaxonomy['post_types'] ) ) {
				$aTaxonomies[] = $aTaxonomy['taxonomy'];
			}
		}

		return $aTaxonomies;
	}
}
