<?php

namespace WilcityOpenTable\Controllers;

use WilcityOpenTable\Helpers\App;

class AdminNotification
{
    public function __construct()
    {
        add_action('admin_notices', [$this, 'requiredParentPlugin']);
    }
    
    public function requiredParentPlugin()
    {
        if (current_user_can('activate_plugins')) {
            $plugin = basename(plugin_dir_path(dirname(__FILE__, 2)));
            
            if (!App::isWSSetup()) {
                ?>
                <div class="notice notice-error" style="padding: 20px; border-left:  4px solid #dc3232; color: red;">
                    In order to use <?php echo $plugin; ?> plugin, please install and setup
                    <a href="https://documentation.wilcity
                    .com/knowledgebase/how-to-auto-update-wilcity-wordpress-theme/" target="_blank">Wilcity Service
                        Client plugin</a>
                </div>
                <?php
            }
        }
    }
}
