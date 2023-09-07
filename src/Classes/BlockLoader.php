<?php

namespace Morningtrain\WP\Blocks\Classes;

use Morningtrain\WP\Blocks\Blocks;
use Symfony\Component\Finder\Finder;

class BlockLoader
{
    protected string $cacheFilename = '_cache.php';
    protected array $blockPaths = [];

    /**
     * Look for block.json files within path and register them as blocks.
     * Sibling .php files will be loaded alongside their block.json files
     *
     * Note: That this class needs to be initialized first.
     *
     * @param  string  $path  The absolute path to loop through
     *
     * @return void
     * @throws \Exception if supplied $path is not a directory
     * @see Blocks::registerBlockDirectory() Use this method instead
     *
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

    /**
     * Initialize the Block Loader
     *
     * @return void
     * @see Blocks::setup()
     *
     */
    public function init(): void
    {
        \add_action('init', [$this, 'loadRegisteredBlocks']);
    }

    /**
     * Finds and loads blocks within all registered paths
     *
     * @return void
     */
    public function loadRegisteredBlocks(): void
    {
        if (empty($this->blockPaths)) {
            return;
        }

        foreach ($this->blockPaths as $blockPath) {
            $this->loadBlocksInPath($blockPath);
        }
    }

    /**
     * Finds and loads blocks within a path
     *
     * @param  string  $path
     *
     * @return void
     */
    protected function loadBlocksInPath(string $path): void
    {
        $cacheFile = $path . "/" . $this->cacheFilename;
        if (\wp_get_environment_type() === 'production' && file_exists($cacheFile)) {
            $this->loadBlocksPathCacheFile($cacheFile);

            return;
        }

        $blocks = [];
        $metaFiles = $this->locateBlocksInPath($path);
        foreach ($metaFiles as $metaFile) {
            $blocks[$metaFile] = $this->locateBlockDependencies($metaFile);
        }

        if (\wp_get_environment_type() === 'production') {
            $this->updateBlocksPathCacheFile($cacheFile, $blocks);
        }

        $this->loadBlocks($blocks);
    }

    /**
     * Finds all block.json files within a path
     *
     * @param  string  $path
     *
     * @return array An array containing the full paths of block.json files
     */
    protected function locateBlocksInPath(string $path): array
    {
        $blocks = [];

        $finder = new Finder();
        $finder->files()->name('block.json')->in($path);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $blocks[] = $file->getRealPath();
            }
        }

        return $blocks;
    }

    /**
     * Finds all sibling .php files
     * Ignores files matching *.asset.php
     *
     * @param  string  $blockMetaFile
     *
     * @return array An array of full .php file paths
     */
    protected function locateBlockDependencies(string $blockMetaFile): array
    {
        $dir = dirname($blockMetaFile);
        $dependencies = [];

        $finder = new Finder();
        $finder->files()->name('*.php')->notName('*.asset.php')->in($dir)->depth('== 0');
        foreach ($finder as $file) {
            $dependencies[] = $file->getRealPath();
        }

        return $dependencies;
    }

    /**
     * Registers blocks and loads their dependencies
     *
     * @param  array{metaFile: array{dependency: string}}  $blocks  An array of blocks and their .php file dependencies
     *
     * @return void
     */
    protected function loadBlocks(array $blocks): void
    {
        foreach ($blocks as $metaFile => $dependencies) {
            $this->loadBlock($metaFile, $dependencies);
        }
    }

    /**
     * Register a single block by its meta file and load its dependencies
     *
     * @param  string  $blockMetaFile
     * @param  array  $dependencies  .php files to require
     *
     * @return void
     */
    protected function loadBlock(string $blockMetaFile, array $dependencies = []): void
    {
        if (! empty($dependencies)) {
            foreach ($dependencies as $dependency) {
                require_once $dependency;
            }
        }
        \register_block_type($blockMetaFile);
    }

    /**
     * Creates or updates a cache file with all relevant block data
     *
     * @param $cacheFile
     * @param $blocksData
     *
     * @return void
     */
    protected function updateBlocksPathCacheFile($cacheFile, $blocksData): void
    {
        \file_put_contents($cacheFile, "<?php return " . var_export($blocksData, true) . ";");
    }

    /**
     * Load blocks defined in a cache file
     *
     * @param $cacheFile
     *
     * @return void
     */
    protected function loadBlocksPathCacheFile($cacheFile): void
    {
        $this->loadBlocks(require $cacheFile);
    }

    /**
     * Delete cache files in registered paths
     *
     * @return array
     */
    public function deleteCacheFiles(): array
    {
        $deletedFiles = [];
        foreach ($this->blockPaths as $path) {
            $cacheFile = $path . "/" . $this->cacheFilename;
            if (str_starts_with($cacheFile, WP_CONTENT_DIR) && file_exists($cacheFile)) {
                unlink($cacheFile);
                $deletedFiles[] = $cacheFile;
            }
        }

        return $deletedFiles;
    }
}
