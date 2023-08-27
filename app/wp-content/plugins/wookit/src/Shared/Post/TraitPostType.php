<?php


namespace WooKit\Shared\Post;


use Exception;
use WooKit\Shared\AutoPrefix;

trait TraitPostType
{

    /**
     * @throws Exception
     */
    public function isPostType($id, $postType): bool
    {
        if (get_post_field('post_type', $id) != $postType) {
            throw new Exception(sprintf(esc_html__('Unfortunately, this item is not a %s',
                'wookit'), $postType));
        }

        return true;
    }

    public function getPostType(string $configPath): string
    {
        $aConfig = include trailingslashit($configPath) . 'PostType.php';

        return AutoPrefix::namePrefix($aConfig['post_type']);
    }
}
