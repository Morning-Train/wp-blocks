<?php

namespace Morningtrain\WP\Blocks\Classes;

class Service
{
    protected static $blockDirectories = [];
    protected static $isInitialized = false;

    public static function init()
    {
        \add_action('init', [static::class, 'registerBlocks']);
    }

    public static function registerBlockDirectory(string $dir)
    {
        static::$blockDirectories[$dir] = $dir;
    }

    public static function registerBlocks()
    {
        foreach (static::$blockDirectories as $blockDirectory) {
            static::registerBlock($blockDirectory);
        }
    }

    public static function registerBlock(string $dir)
    {
        if (file_exists($dir . "/block.php")) {
            require $dir . "/block.php";
        } elseif (file_exists($dir . "/block.json")) {
            \register_block_type($dir . "/block.json");
        }
    }

}
