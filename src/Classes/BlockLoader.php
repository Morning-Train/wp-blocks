<?php

namespace Morningtrain\WP\Blocks\Classes;

use Symfony\Component\Finder\Finder;

class BlockLoader
{
    protected array $blockPaths = [];

    /**
     * Register a directory containing blocks
     *
     * @throws \Exception if supplied $path is not a directory
     */
    public function registerBlockPath(string $path): void
    {
        if (! is_dir($path)) {
            throw new \Exception("Path is not a directory \"{$path}\"");
        }

        if (in_array($path, $this->blockPaths)) {
            return;
        }

        $this->blockPaths[] = $path;
    }

    public function init()
    {
        if (empty($this->blockPaths)) {
            return;
        }

        \add_action('init', [$this, 'loadRegisteredBlocks']);
    }


    public function loadRegisteredBlocks()
    {
        foreach ($this->blockPaths as $blockPath) {
            $this->loadBlocksInPath($blockPath);
        }
    }

    protected function loadBlocksInPath(string $path)
    {
        // TODO: Do some caching here first

        $finder = new Finder();
        $finder->files()->name('block.json')->in($path);

        if (! $finder->hasResults()) {
            return;
        }

        foreach ($finder as $file) {
            $this->loadBlock($file->getRealPath());
        }
    }

    protected function loadBlock(string $blockMetaFile)
    {
        $dir = dirname($blockMetaFile);
        $phpFiles = [];

        $finder = new Finder();
        $finder->files()->name('*.php')->in($dir)->depth('== 0');
        foreach ($finder as $file) {
            $phpFiles[] = $file->getRealPath();
            require_once $file->getRealPath();
        }

        \register_block_type($blockMetaFile);
    }
}
