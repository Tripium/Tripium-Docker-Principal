<?php


namespace WooKit\Slidein\Services\Post;


use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Post\Query\IQueryPost;
use WooKit\Shared\Post\Query\QueryPost;


class SlideinQueryService extends QueryPost implements IQueryPost
{

    public function parseArgs(): IQueryPost
    {
        $this->aArgs = $this->commonParseArgs();

        $this->aArgs['post_type'] = $this->getPostType();

        return $this;
    }

    public function getPostType(): string
    {
        $aConfig = include(plugin_dir_path(__FILE__) . '../../Configs/PostType.php');
        return AutoPrefix::namePrefix($aConfig['post_type']);
    }
}
