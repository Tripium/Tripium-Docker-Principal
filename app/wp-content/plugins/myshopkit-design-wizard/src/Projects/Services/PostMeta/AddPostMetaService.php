<?php


namespace MyshopKitDesignWizard\Projects\Services\PostMeta;

use MyshopKitDesignWizard\Projects\Services\PostMeta\PostMetaService;

class AddPostMetaService extends PostMetaService {
	public function addPostMeta( array $aRawData ): array {
		$this->setIsUpdate( false );
		$this->setRawData( $aRawData );

		return $this->performSaveData();
	}
}
