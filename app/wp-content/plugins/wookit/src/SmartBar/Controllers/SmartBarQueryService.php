<?php


namespace WooKit\SmartBar\Controllers;


use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Post\Query\IQueryPost;
use WooKit\Shared\Post\Query\QueryPost;

class SmartBarQueryService extends QueryPost implements IQueryPost {
	public function getPostType(): string {
		$this->postType = AutoPrefix::namePrefix( 'smartbar' );

		return $this->postType;
	}

	public function parseArgs(): IQueryPost {
		$this->aArgs              = $this->commonParseArgs();
		$this->aArgs['post_type'] = $this->getPostType();

		return $this;
	}
}
