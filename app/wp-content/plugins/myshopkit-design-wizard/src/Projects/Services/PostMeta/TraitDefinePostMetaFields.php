<?php


namespace MyshopKitDesignWizard\Projects\Services\PostMeta;


trait TraitDefinePostMetaFields {
	protected array $aFields = [];

	public function defineFields(): array {
		$this->aFields = [
			'content' => [
				'key'              => 'project_content',
				'assert'           => [
					'callbackFunc' => 'notEmpty'
				]
			],
            'metadata' => [
                'key'              => 'project_metadata'
            ],
			'taxonomies' => [
				'key'              => 'project_taxonomies'
			],
			'thumbnail' => [
				'key'              => 'project_thumbnail_id',
				'sanitizeCallback' => 'sanitize_text_field',
				'assert'           => [
					'callbackFunc' => 'isJson'
				]
			]
		];

		return $this->aFields;
	}
}
