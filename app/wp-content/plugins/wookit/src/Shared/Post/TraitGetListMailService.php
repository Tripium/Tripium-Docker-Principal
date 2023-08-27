<?php


namespace WooKit\Shared\Post;


trait TraitGetListMailService
{
    public function getListMailService(): array
    {
        return apply_filters(WOOKIT_HOOK_PREFIX . 'Filter/Shared/Post/TraitGetListMailService', []);
    }
}
