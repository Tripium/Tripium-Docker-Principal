<?php

namespace WilokeListingTools\Framework\Helpers;

use Exception;
use NumberFormatter;

class Price
{
    public static function moneyFormat($price)
    {
        if (class_exists('NumberFormatter')) {
            $countryLocale = GetWilokeSubmission::getField('country_locale');
            $countryLocale = empty($countryLocale) ? get_locale() : $countryLocale;
            $fmt = new NumberFormatter($countryLocale, NumberFormatter::DECIMAL);

            return apply_filters(
                'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/Price/moneyFormat',
                $fmt->format($price),
                $price
            );
        } else {
            setlocale(LC_MONETARY, get_locale());
            $aLocale = localeconv();
            return apply_filters(
                'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/Price/renderPrice',
                number_format($price, 2, $aLocale['decimal_point'], $aLocale['thousands_sep']),
                $price
            );
        }
    }

    public static function renderPrice($price)
    {
        if (class_exists('NumberFormatter')) {
            $countryLocale = GetWilokeSubmission::getField('country_locale');
            if (empty($countryLocale)) {
                $currencyCode = GetWilokeSubmission::getField('currency_code');
                $fmt = new NumberFormatter(get_locale(), NumberFormatter::CURRENCY);

                return apply_filters(
                    'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/Price/renderPrice',
                    $fmt->formatCurrency($price, $currencyCode),
                    $price
                );
            } else {
                $fmt = new NumberFormatter($countryLocale, NumberFormatter::CURRENCY);
                $currencyCode = $fmt->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL);
                $priceFormat = $fmt->formatCurrency($price, $currencyCode);
                if (!$priceFormat && !empty($price)) {
                    $priceFormat
                        = esc_html__("Your Country Locale setting is not valid. Please go to Wiloke Submission -> Country Locale and resolve it",
                        "wiloke-listing-tools");
                }

                return apply_filters(
                    'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/Price/renderPrice',
                    $priceFormat,
                    $price
                );
            }
        } else {
            try {
                if (function_exists("money_format")) {
                    $priceFormat = money_format('%i', $price);
                } else {
                    $aLocale = localeconv();
                    $priceFormat = number_format($price, 2, $aLocale['decimal_point'], $aLocale['thousands_sep']);
                }
                setlocale(LC_MONETARY, get_locale());
                return apply_filters(
                    'wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/Price/renderPrice',
                    $priceFormat,
                    $price
                );
            }catch(Exception $ex) {
                return $ex->getMessage();
            }
        }
    }
}
