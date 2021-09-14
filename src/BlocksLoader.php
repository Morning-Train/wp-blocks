<?php

namespace Morningtrain\WP\Blocks;


use Symfony\Component\Finder\Finder;

class BlocksLoader
{

    public static function registerBlocksInDir(string $dir_path)
    {
        $finder = new Finder();
        $finder->files()->name('*Block.php')->in($dir_path);

        if (!$finder->hasResults()) {
            return;
        }

        $hook_files = [];
        foreach ($finder as $file) {
            $hook_files[] = $file->getRealPath();
        }

    }}