<?php

namespace WooKit\Shared\Locale;


trait TrainLocale
{
    public function convertCountryCodeToLocale($currency): string
    {
        $aLocales = include WOOKIT_PATH . 'src/Shared/Configs/Locales.php';
        $countryCode = array_search($currency, (include WOOKIT_PATH . 'src/Shared/Configs/CountryCodeAndCurrency.php'));
        switch ($currency) {
            case 'EUR':
                $locale = 'en-GB';
                break;
            case 'USD':
                $locale = 'en-US';
                break;
            default:
                foreach ($aLocales as $locale) {
                    $localeRegion = locale_get_region($locale);
                    $localeLanguage = locale_get_primary_language($locale);
                    $aLocale = [
                        'language' => $localeLanguage,
                        'region'   => $localeRegion
                    ];
                    if (strtoupper($countryCode) == $localeRegion) {
                        $locale = locale_compose($aLocale);
                        break;
                    }
                }
                break;
        }

        return !empty($locale)?$locale:'en-EN';
    }
}