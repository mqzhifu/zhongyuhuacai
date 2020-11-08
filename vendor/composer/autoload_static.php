<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit788e0c6ec5c75d08b3ab8265c924e6c7
{
    public static $files = array (
        'decc78cc4436b1292c6c0d151b19445c' => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'z' => 
        array (
            'zhongyuhuacai\\' => 14,
        ),
        'p' => 
        array (
            'phpseclib\\' => 10,
        ),
        'P' => 
        array (
            'PhpAmqpLib\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'zhongyuhuacai\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
        'phpseclib\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib',
        ),
        'PhpAmqpLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-amqplib/php-amqplib/PhpAmqpLib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit788e0c6ec5c75d08b3ab8265c924e6c7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit788e0c6ec5c75d08b3ab8265c924e6c7::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
