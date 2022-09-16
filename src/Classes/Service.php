<?php

namespace Morningtrain\WP\Blocks\Classes;

class Service
{
    protected static array $buildDirectories = [];
    protected static ?string $blockDirectory = null;
    protected static ?string $patternDirectory = null;
    protected static bool $isInitialized = false;

    public static function init(string $blocksPath)
    {
        static::$blockDirectory = trailingslashit($blocksPath);
        static::$patternDirectory = static::$blockDirectory . '_patterns/';

        \add_action('init', [static::class, 'registerBlocks']);
    }

    public static function getBlocksDirectory(): ?string
    {
        return static::$blockDirectory;
    }

    public static function setPatternDirectory(string $path)
    {
        static::$patternDirectory = $path;
    }

    public static function getPatternDirectory(): ?string
    {
        if (! is_dir(static::$patternDirectory)) {
            mkdir(static::$patternDirectory);
        }

        return static::$patternDirectory;
    }

    public static function getPartsDirectory(): string
    {
        return \trailingslashit(\get_template_directory()) . 'parts/';
    }

    public static function addBuildDirectory(string $dir)
    {
        static::$buildDirectories[$dir] = $dir;
    }

    public static function registerBlocks()
    {
        foreach (static::$buildDirectories as $blockDirectory) {
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
