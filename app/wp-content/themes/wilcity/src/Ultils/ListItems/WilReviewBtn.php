<?php

namespace Wilcity\Ultils\ListItems;

class WilReviewBtn extends RenderableAbstract implements RenderableInterface
{
    public function render(): string
    {
        $postID = $this->getAttribute('postId', '');
        if (empty($postID)) {
            global $post;
            if (empty($post)) {
                return '';
            }

            $postID = $post->ID;
        }
        $hasWrapperForIcon = $this->getAttribute('hasWrapperForIcon', 'no');
        $classes = $this->getAttribute('wrapperClasses', 'list_link__2rDA1 text-ellipsis color-primary--hover');
        ob_start();
        ?>
        <wil-review-btn icon="<?php echo esc_attr($this->getAttribute('icon', 'la la-star-o')); ?>"
                        has-wrapper-for-icon="<?php echo  esc_attr($hasWrapperForIcon); ?>"
                        :post-id="<?php echo abs($postID); ?>"
                        btn-name="<?php echo esc_attr($this->getAttribute('btnName')); ?>"
                        wrapper-classes="<?php echo esc_attr($classes); ?>"></wil-review-btn>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
