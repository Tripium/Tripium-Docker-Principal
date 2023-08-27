<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfc60edfc1aadfd5f1d542f532ff09600
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WilcityOpenTable\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WilcityOpenTable\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfc60edfc1aadfd5f1d542f532ff09600::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfc60edfc1aadfd5f1d542f532ff09600::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
