<?php

namespace MyshopKitDesignWizard\Shared\Post;

use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;

trait TraitPostHelps
{
    public function getPostTypes()
    {
        return apply_filters(MYSHOPKIT_MB_HOOK_PREFIX . 'src/Shared/Post/TraitPostHelps/getListPostType', [
            'manual' => AutoPrefix::namePrefix('manual'),
            'badge'  => AutoPrefix::namePrefix('badge')
        ]);
    }
}