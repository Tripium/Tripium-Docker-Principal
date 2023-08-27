<?php

namespace WilokeGoogleAuthenticator\Controllers;

class AdminEnqueueScripts
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }
    
    public function enqueueScripts()
    {
        global $pagenow;
        if ($pagenow !== 'user-edit.php') {
            return false;
        }
        
        wp_enqueue_script('admin-otp', WILOKE_GOOGLE_AUTHENTICATOR_URL.'assets/js/admin.js', ['jquery'],
          WILOKE_GOOGLE_AUTHENTICATOR_VERSION, true);
    }
}
