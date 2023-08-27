<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitecfa6b705ce4ad95c03bb682a69e6725
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WilokeCommandLine\\' => 18,
            'Webmozart\\Assert\\' => 17,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Ctype\\' => 23,
        ),
        'M' => 
        array (
            'MyshopKitDesignWizard\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WilokeCommandLine\\' => 
        array (
            0 => __DIR__ . '/..' . '/wilokecom/phpcli/src',
        ),
        'Webmozart\\Assert\\' => 
        array (
            0 => __DIR__ . '/..' . '/webmozart/assert/src',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'MyshopKitDesignWizard\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitecfa6b705ce4ad95c03bb682a69e6725::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitecfa6b705ce4ad95c03bb682a69e6725::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitecfa6b705ce4ad95c03bb682a69e6725::$classMap;

        }, null, ClassLoader::class);
    }
}
