<?php

use WooKit\MailServices\Klaviyo\Controllers\KlaviyoController;

define( 'KLAVIYO_URL', plugin_dir_url( __FILE__));
define( 'KLAVIYO_PATH', plugin_dir_path( __FILE__));

new KlaviyoController();
