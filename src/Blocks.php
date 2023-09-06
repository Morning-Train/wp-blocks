<?php

namespace Morningtrain\WP\Blocks;

use Morningtrain\WP\Blocks\Classes\BlockLoader;
use Morningtrain\WP\Blocks\Classes\Service;

class Blocks
{
    protected static BlockLoader $blockLoader;
    protected static Service $service;
    protected static bool $isInitialized = false;

    /**
     * Register a directory containing blocks
     *
     * @param  string  $path
     * @throws \Exception Throws is $path is not a directory
     */
    public static function setup(string $path): void
    {
        if (! isset(static::$service)) {
            static::$service = new Service();
        }

        static::registerBlockDirectory($path);

        if (! static::$isInitialized) {
            static::init();
        }
    }

    /**
     * Register a directory containing blocks
     *
     * @param  string  $path
     * @throws \Exception Throws is $path is not a directory
     */
    public static function registerBlockDirectory(string $path): void
    {
        if (! isset(static::$blockLoader)) {
            static::$blockLoader = new BlockLoader();
        }

        static::$blockLoader->registerBlockPath($path);
    }

    protected static function init(): void
    {
        static::$blockLoader->init();
        static::$service->init();

        static::$isInitialized = true;
    }
}
