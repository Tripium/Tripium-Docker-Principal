<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2b65d1e48de8ac5dff2323b83303eb66
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WilcityVR\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WilcityVR\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2b65d1e48de8ac5dff2323b83303eb66::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2b65d1e48de8ac5dff2323b83303eb66::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
