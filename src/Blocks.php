<?php

namespace Morningtrain\WP\Blocks;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Blocks\Classes\Block;
use Morningtrain\WP\Blocks\Classes\Pattern;
use Morningtrain\WP\Blocks\Classes\Service;

class Blocks
{
    public static function setup(string $blocksPath, string|array $buildPath)
    {
        Service::init($blocksPath);
        foreach ((array) $buildPath as $p) {
            if (! is_dir($p)) {
                continue;
            }
            $iterator = new \DirectoryIterator($p);
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->getType() !== 'dir' || $fileInfo->isDot()) {
                    continue;
                }
                Service::addBuildDirectory($fileInfo->getPathname());
            }
        }
    }

    public static function setPatternDirectory(string $path)
    {
        Service::setPatternDirectory($path);
    }

    public static function create(string $dir): Block
    {
        return new Block($dir);
    }

    /**
     * Register a new block pattern
     *
     * @param  string  $namespace
     * @param  string  $name
     *
     * @return Pattern
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_pattern/
     */
    public static function pattern(string $namespace, string $name): Pattern
    {
        return new Pattern($namespace, $name);
    }

}
