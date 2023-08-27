<?php

namespace MyshopKitDesignWizard\Projects\Services\Post;


use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;

trait TraitDefinePostFields
{
	private array $aFields = [];

	public function defineFields(): array
	{
		$this->aFields = [
			'status'     => [
				'key'              => 'post_status',
				'sanitizeCallback' => [$this, 'sanitizePostStatus'],
				'value'            => 'active',
				'assert'           => [
					'callbackFunc' => 'inArray',
					'expected'     => ['active', 'deactive', 'trash']
				]
			],
			'id'         => [
				'key'              => 'ID',
				'sanitizeCallback' => 'abs',
				'value'            => 0
			],
			'parentID'   => [
				'key'              => 'post_parent',
				'sanitizeCallback' => 'abs',
				'value'            => 0
			],
			'label'      => [
				'key'              => 'post_title',
				'sanitizeCallback' => 'sanitize_text_field',
				'value'            => 'wiloke smart mockup',
				'assert'           => [
					'callbackFunc' => 'notEmpty'
				]
			],
			'thumbnail'  => [
				'key'              => 'thumbnail',
				'sanitizeCallback' => 'sanitize_text_field'
			],
			'taxonomies' => [
				'key'              => 'taxonomies',
				'sanitizeCallback' => 'sanitize_text_field'
			],
			'type'       => [
				'key'        => 'post_type',
				'value'      => AutoPrefix::namePrefix('my_projects'),
				'isReadOnly' => true
			],
			'author'     => [
				'key'        => 'post_author',
				'isRequired' => true,
				'isReadOnly' => true,
				'value'      => get_current_user_id()
			]
		];

		return $this->aFields;
	}

	public function sanitizePostStatus($value): string
	{
		switch ($value) {
			case 'active':
				$status = 'publish';
				break;
			case 'trash':
				$status = 'trash';
				break;
			default:
				$status = 'draft';
				break;
		}
		return $status;
	}
}
