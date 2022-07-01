<?php

namespace Morningtrain\WP\Blocks;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Blocks\Classes\Block;
use Morningtrain\WP\Blocks\Classes\Service;

class Blocks
{
    public static function loadDir(string|array $path)
    {
        Service::init();
        foreach ((array) $path as $p) {
            if (! is_dir($p)) {
                continue;
            }
            $iterator = new \DirectoryIterator($p);
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->getType() !== 'dir' || $fileInfo->isDot()) {
                    continue;
                }
                Service::registerBlockDirectory($fileInfo->getPathname());
            }
        }
    }

    public static function create(string $dir): Block
    {
        return new Block($dir);
    }
}
