<?php

namespace WooKit\PostScript\Controllers;

use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Locale\TrainLocale;

class PostScriptController
{
    use TrainLocale;

    const WOOKIT_GLOBAL = 'Wookit';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function enqueueScripts(): bool
    {
        $aDataCampaigns = apply_filters(WOOKIT_PREFIX .
            'Filter/PostScript/Controllers/PostScriptController/getCampaignsActive', [
            'popup'    => [],
            'smartbar' => [],
            'slidein'  => [],
        ]);
        $currency = !function_exists('get_woocommerce_currency') ? 'USD' : get_woocommerce_currency();
        wp_localize_script('jquery', self::WOOKIT_GLOBAL,
            array_merge([
                'restBase' => rest_url(WOOKIT_REST_BASE),
                'currency' => $currency,
                'locale'   => $this->convertCountryCodeToLocale($currency),
            ], $aDataCampaigns));
        wp_enqueue_script(
            AutoPrefix::namePrefix('post-script'),
            'https://wookit-client.netlify.app/main.js',
            ['jquery'],
            WOOKIT_VERSION,
            true
        );
        return true;
    }
}
