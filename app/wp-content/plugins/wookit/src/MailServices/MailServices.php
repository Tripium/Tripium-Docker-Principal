<?php
use WooKit\MailServices\ActiveCampaign\Controllers\ActiveCampaignController;
use WooKit\MailServices\CampaignMonitor\Controllers\CampaignMonitorController;
use WooKit\MailServices\General\Controllers\GeneralMailServicesController;
use WooKit\MailServices\GetResponse\Controllers\GetResponseController;
use WooKit\MailServices\iContact\src\Controllers\iContactController;
use WooKit\MailServices\Klaviyo\Controllers\KlaviyoController;
use WooKit\MailServices\MailChimp\Controllers\MailChimpController;

include (plugin_dir_path(__FILE__). 'iContact/src/icontact-api-php-master/lib/iContactApi.php');
define('MYSHOPKIT_LINK', 'https://doc.myshopkit.app'. WOOKIT_DS);

new GeneralMailServicesController();
new ActiveCampaignController();
new CampaignMonitorController();
new GetResponseController();
new iContactController();
new MailChimpController();
new KlaviyoController();


