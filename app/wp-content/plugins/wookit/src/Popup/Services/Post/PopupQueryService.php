<?php


namespace WooKit\Popup\Services\Post;


use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Post\Query\IQueryPost;
use WooKit\Shared\Post\Query\PostSkeleton;
use WooKit\Shared\Post\Query\QueryPost;

class PopupQueryService extends QueryPost implements IQueryPost {
	public function getPostType(): string {
		$this->postType = AutoPrefix::namePrefix( 'popup' );

		return $this->postType;
	}

	public function parseArgs(): IQueryPost {
		$this->aArgs              = $this->commonParseArgs();
		$this->aArgs['post_type'] = $this->getPostType();

		return $this;
	}
}
