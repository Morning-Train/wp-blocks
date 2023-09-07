<?php

namespace Morningtrain\WP\Blocks;

use Morningtrain\WP\Blocks\Classes\BlockLoader;
use Morningtrain\WP\Blocks\Classes\Cli;
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

    /**
     * Initialize Service
     *
     * @return void
     */
    protected static function init(): void
    {
        static::$blockLoader->init();
        static::$service->init();

        if (class_exists("\WP_CLI")) {
            \WP_CLI::add_command('wp-blocks', new Cli());
        }

        static::$isInitialized = true;
    }

    /**
     * Get access to the active Block Loader
     *
     * @return BlockLoader
     */
    public static function getBlockLoader(): BlockLoader
    {
        return static::$blockLoader;
    }
}
