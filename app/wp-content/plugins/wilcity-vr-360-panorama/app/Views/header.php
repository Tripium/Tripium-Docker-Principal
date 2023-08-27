<?php
global $post;
$coverImg = \WilokeListingTools\Framework\Helpers\GetSettings::getCoverImage($post->ID, 'large');
?>
<header class="listing-detail_header__18Cfs">
    <div class="listing-detail_img__3DyYX pos-a-full bg-cover"
         style="background-image: url(<?php echo esc_url($coverImg); ?>);">
        <iframe id="myframe"
                src="<?php echo esc_url($this->vrSrc); ?>"
                frameborder="0"
                style="overflow:hidden;
                overflow-x:hidden;
                overflow-y:hidden;
                height:100%;width:100%;">
        </iframe>
    </div>
</header>
