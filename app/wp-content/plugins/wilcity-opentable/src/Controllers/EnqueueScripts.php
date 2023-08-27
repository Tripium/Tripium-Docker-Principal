<?php

namespace WilcityOpenTable\Controllers;

class EnqueueScripts
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
    }
    
    public function registerScripts()
    {
        wp_register_script('wilcity-opentable', WILCITY_OPENTABLE_URL.'source/js/script.js', ['jquery'],
          WILCITY_OPENTABLE_VERSION, true);
        wp_localize_script(
          'jquery-migrate',
          'WILCITY_OPENTABLE',
          [
            'css' => WILCITY_OPENTABLE_URL.'source/css/'
          ]
        );
    }
}
