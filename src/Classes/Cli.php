<?php

namespace Morningtrain\WP\Blocks\Classes;

use Morningtrain\WP\Blocks\Blocks;

class Cli
{
    /**
     * Delete cache files
     *
     * @return void
     */
    public function deleteCacheFiles(): void
    {
        $deletedCacheFiles = Blocks::getBlockLoader()->deleteCacheFiles();
        $num = count($deletedCacheFiles);
        \WP_CLI::success("Deleted $num file(s)");
    }
}
