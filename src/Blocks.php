<?php

namespace Morningtrain\WP\Blocks;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Blocks\Classes\Block;

class Blocks
{
    private static ?string $buildDir = null;
    private static ?string $buildUrl = null;

    public static function loadDir(string|array $path)
    {
        Loader::create($path);
    }

    public static function create(string $nameSpace): Block
    {
        $block = new Block($nameSpace);
        if (static::$buildDir !== null) {
            $block->buildDir(static::$buildDir);
        }
        if (static::$buildUrl !== null) {
            $block->buildUrl(static::$buildUrl);
        }

        return $block;
    }

    public static function setBuildDir(string $path)
    {
        static::$buildDir = $path;
    }

    public static function setBuildUrl(string $url)
    {
        static::$buildUrl = $url;
    }
}
