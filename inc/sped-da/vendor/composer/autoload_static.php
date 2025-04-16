<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit69fb0d985c43c6d1a70e19c677ad3e38
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'N' => 
        array (
            'NFePHP\\DA\\' => 10,
            'NFePHP\\Common\\' => 14,
        ),
        'L' => 
        array (
            'League\\Flysystem\\' => 17,
        ),
        'C' => 
        array (
            'Com\\Tecnick\\Color\\' => 18,
            'Com\\Tecnick\\Barcode\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'NFePHP\\DA\\' => 
        array (
            0 => __DIR__ . '/..' . '/nfephp-org/sped-da/src',
        ),
        'NFePHP\\Common\\' => 
        array (
            0 => __DIR__ . '/..' . '/nfephp-org/sped-common/src',
        ),
        'League\\Flysystem\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/flysystem/src',
        ),
        'Com\\Tecnick\\Color\\' => 
        array (
            0 => __DIR__ . '/..' . '/tecnickcom/tc-lib-color/src',
        ),
        'Com\\Tecnick\\Barcode\\' => 
        array (
            0 => __DIR__ . '/..' . '/tecnickcom/tc-lib-barcode/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'F' => 
        array (
            'ForceUTF8\\' => 
            array (
                0 => __DIR__ . '/..' . '/neitanod/forceutf8/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit69fb0d985c43c6d1a70e19c677ad3e38::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit69fb0d985c43c6d1a70e19c677ad3e38::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit69fb0d985c43c6d1a70e19c677ad3e38::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit69fb0d985c43c6d1a70e19c677ad3e38::$classMap;

        }, null, ClassLoader::class);
    }
}
