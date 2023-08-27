<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit74345240e26105b31235d916a0dd8818
{
    public static $files = array (
        '6e3fae29631ef280660b3cdad06f25a8' => __DIR__ . '/..' . '/symfony/deprecation-contracts/function.php',
        'a4a119a56e50fbb293281d9a48007e0e' => __DIR__ . '/..' . '/symfony/polyfill-php80/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '0d59ee240a4cd96ddbb4ff164fccea4d' => __DIR__ . '/..' . '/symfony/polyfill-php73/bootstrap.php',
        'c65d09b6820da036953a371c8c73a9b1' => __DIR__ . '/..' . '/facebook/graph-sdk/src/Facebook/polyfills.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WilokeListingTools\\' => 19,
        ),
        'T' => 
        array (
            'Tests\\' => 6,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Php80\\' => 23,
            'Symfony\\Polyfill\\Php73\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Contracts\\Translation\\' => 30,
            'Symfony\\Contracts\\Service\\' => 26,
            'Symfony\\Contracts\\HttpClient\\' => 29,
            'Symfony\\Component\\Translation\\' => 30,
            'Symfony\\Component\\HttpClient\\' => 29,
            'Stripe\\' => 7,
        ),
        'R' => 
        array (
            'ReallySimpleJWT\\Helper\\' => 23,
            'ReallySimpleJWT\\Exception\\' => 26,
            'ReallySimpleJWT\\' => 16,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'Psr\\Container\\' => 14,
        ),
        'K' => 
        array (
            'Konekt\\PdfInvoice\\' => 18,
        ),
        'F' => 
        array (
            'Facebook\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WilokeListingTools\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Tests\\' => 
        array (
            0 => __DIR__ . '/..' . '/rbdwllr/reallysimplejwt/tests',
        ),
        'Symfony\\Polyfill\\Php80\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php80',
        ),
        'Symfony\\Polyfill\\Php73\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php73',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Contracts\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation-contracts',
        ),
        'Symfony\\Contracts\\Service\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/service-contracts',
        ),
        'Symfony\\Contracts\\HttpClient\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/http-client-contracts',
        ),
        'Symfony\\Component\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation',
        ),
        'Symfony\\Component\\HttpClient\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/http-client',
        ),
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'ReallySimpleJWT\\Helper\\' => 
        array (
            0 => __DIR__ . '/..' . '/rbdwllr/reallysimplejwt/src/Helper',
        ),
        'ReallySimpleJWT\\Exception\\' => 
        array (
            0 => __DIR__ . '/..' . '/rbdwllr/reallysimplejwt/src/Exception',
        ),
        'ReallySimpleJWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/rbdwllr/reallysimplejwt/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Konekt\\PdfInvoice\\' => 
        array (
            0 => __DIR__ . '/..' . '/konekt/pdf-invoice/src',
        ),
        'Facebook\\' => 
        array (
            0 => __DIR__ . '/..' . '/facebook/graph-sdk/src/Facebook',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/nesbot/carbon/src',
    );

    public static $prefixesPsr0 = array (
        'U' => 
        array (
            'UpdateHelper\\' => 
            array (
                0 => __DIR__ . '/..' . '/kylekatarnls/update-helper/src',
            ),
        ),
        'P' => 
        array (
            'PayPal' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/rest-api-sdk-php/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Attribute' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Attribute.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
        'JsonException' => __DIR__ . '/..' . '/symfony/polyfill-php73/Resources/stubs/JsonException.php',
        'PhpToken' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
        'Stringable' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Stringable.php',
        'UnhandledMatchError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
        'ValueError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/ValueError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit74345240e26105b31235d916a0dd8818::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit74345240e26105b31235d916a0dd8818::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInit74345240e26105b31235d916a0dd8818::$fallbackDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit74345240e26105b31235d916a0dd8818::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit74345240e26105b31235d916a0dd8818::$classMap;

        }, null, ClassLoader::class);
    }
}
