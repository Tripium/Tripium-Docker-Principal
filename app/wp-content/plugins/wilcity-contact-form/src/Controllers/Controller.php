<?php

namespace WilcityContactForm\Controllers;

use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User;

class Controller
{
    protected function getListingAuthorEmail($postID)
    {
        $post = get_post($postID);
        
        if (!is_wp_error($post) && !empty($post) && General::isPostTypeSubmission($post->post_type)) {
            $email = GetSettings::getPostMeta($post->ID, 'wilcity_email');
            if (empty($email)) {
                $email = User::getField('user_email', $post->post_author);
            }
        }
        
        return $email;
    }
}
