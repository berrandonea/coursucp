<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit19c127b524ffa1ce0c59faf6d2cbabe9
{
    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'Captioning' => 
            array (
                0 => __DIR__ . '/..' . '/captioning/captioning/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit19c127b524ffa1ce0c59faf6d2cbabe9::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}