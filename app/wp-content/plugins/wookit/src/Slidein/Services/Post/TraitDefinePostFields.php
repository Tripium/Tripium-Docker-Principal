<?php
/**
 * Định nghĩa post field tại đây.
 */

namespace WooKit\Slidein\Services\Post;



use Wookit\Shared\Post\TraitPostType;

trait TraitDefinePostFields {
    use TraitPostType;
	private array $aFields = [];

	public function defineFields(): array {
		$this->aFields = [
			'status' => [
				'key'              => 'post_status',
				'sanitizeCallback' => [ $this, 'sanitizePostStatus' ],
				'value'            => 'active',
                'assert'           => [
                    'callbackFunc' => 'notEmpty'
                ]
			],
			'id'     => [
				'key'              => 'ID',
				'sanitizeCallback' => 'abs',
				'value'            => 0
			],
			'title'  => [
				'key'              => 'post_title',
				'sanitizeCallback' => 'sanitize_text_field',
				'value'            => 'my shop kid',
				'assert'           => [
					'callbackFunc' => 'notEmpty'
				]
			],
			'type'   => [
				'key'        => 'post_type',
				'value'      => $this->getPostType(plugin_dir_path( __FILE__) . '../../Configs/'),
				'isReadOnly' => true
			],
			'author' => [
				'key'        => 'post_author',
				'isRequired' => true,
				'isReadOnly' => true,
				'value'      => get_current_user_id()
			]
		];

		return $this->aFields;
	}

	public function sanitizePostStatus( $value ): string {
		return ( $value === 'active' ) ? 'publish' : 'draft';
	}
}
