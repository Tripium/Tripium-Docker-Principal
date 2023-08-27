<?php
define( 'MAILCHIMP_URL', plugin_dir_url( __FILE__));
define( 'MAILCHIMP_PATH', plugin_dir_path( __FILE__));

new \WooKit\MailServices\MailChimp\Controllers\MailChimpController();
