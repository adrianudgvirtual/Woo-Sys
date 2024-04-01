<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8632aff98b8d0ab7dec1f508da93c3e0
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Automattic\\WooCommerce\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Automattic\\WooCommerce\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/woocommerce/src/WooCommerce',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8632aff98b8d0ab7dec1f508da93c3e0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8632aff98b8d0ab7dec1f508da93c3e0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8632aff98b8d0ab7dec1f508da93c3e0::$classMap;

        }, null, ClassLoader::class);
    }
}
