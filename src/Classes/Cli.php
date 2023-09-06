<?php

namespace Morningtrain\WP\Blocks\Classes;

use Morningtrain\WP\Blocks\Blocks;

class Cli
{
    public function deleteCacheFiles()
    {
        $deletedCacheFiles = Blocks::getBlockLoader()->deleteCacheFiles();
        \ray($deletedCacheFiles);
        $num = count($deletedCacheFiles);
        \WP_CLI::success("Deleted $num files");
    }
}
