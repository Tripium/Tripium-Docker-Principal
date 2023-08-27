<?php
/*
Plugin Name: Wiloke Google Authenticator
Description: A useful plugin for WordPress Security
Version: 1.1.3
Text Domain: wiloke-google-authenticator
Domain Path: /languages
Author: Wiloke
Author URI: https://wiloke.com
Plugin URI:: https://wilcityservice.com/wiloke-plugins/wiloke-google-authenticator/
*/

require_once plugin_dir_path(__FILE__).'vendor/autoload.php';

define('WILOKE_GOOGLE_AUTHENTICATOR_VERSION', '1.1.3');
define('WILOKE_GOOGLE_AUTHENTICATOR_PATH', plugin_dir_path(__FILE__));
define('WILOKE_GOOGLE_AUTHENTICATOR_URL', plugin_dir_url(__FILE__));
define('WILOKE_GOOGLE_AUTHENTICATOR_CONFIG_PATH', WILOKE_GOOGLE_AUTHENTICATOR_PATH . 'config/');

use WilokeGoogleAuthenticator\Controllers\AdminEnqueueScripts;
use WilokeGoogleAuthenticator\Controllers\GoogleAuthSettingsController;
use WilokeGoogleAuthenticator\Controllers\GoogleAuthVerificationController;
use WilokeGoogleAuthenticator\Controllers\ThemeOptionController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\AppleLoginController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\DashboardController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\FacebookLoginController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\LoginRedirectionController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\OTPVerificationController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\OTPVerificationFormController;
use WilokeGoogleAuthenticator\Controllers\Wilcity\WilcityAppUserController;
use WilokeGoogleAuthenticator\Helpers\App;
include WILOKE_GOOGLE_AUTHENTICATOR_PATH . 'PHPGangsta_GoogleAuthenticator.php';
if (is_admin()) {
    App::bind('GoogleAuthSettingsController', new GoogleAuthSettingsController);
    App::bind('GoogleAuthSettingsController', new ThemeOptionController);
    App::bind('AdminEnqueueScripts', new AdminEnqueueScripts);
}

App::bind('GoogleAuthVerificationController', new GoogleAuthVerificationController);

add_action('wiloke-listing-tools/run-extension', function() {
    App::bind('LoginRedirection', new LoginRedirectionController);
    App::bind('OTPVerificationForm', new OTPVerificationFormController);
    App::bind('OTPVerificationController', new OTPVerificationController);
    App::bind('DashboardController', new DashboardController);
    App::bind('FacebookLoginController', new FacebookLoginController);
    App::bind('AppleLoginController', new AppleLoginController);
    App::bind('WilcityAppUserController', new WilcityAppUserController);
});

do_action('wiloke-google-authenticator/run-extension');
