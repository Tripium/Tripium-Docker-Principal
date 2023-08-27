<?php

namespace WooKit\Popup\Controllers;

use WooKit\Shared\AutoPrefix;

class PostTypeRegistration
{
    public function __construct()
    {
        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType()
    {
        register_post_type(AutoPrefix::namePrefix('popup'), include WOOKIT_PATH.'src/Popup/Configs/PostType.php');
    }
}
