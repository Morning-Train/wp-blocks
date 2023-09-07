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
            $blocks[] = [
                'metaFile' => $metaFile,
                'phpFiles' => $this->locateBlockDependencies($metaFile),
            ];
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
     * @return string[] An array containing the full paths of block.json files
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
     * @return string[] An array of full .php file paths
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
     * @param  array{ array{ metaFile: string, phpFiles: string[] } }  $blocks  An array of blocks and their .php file dependencies
     *
     * @return void
     */
    protected function loadBlocks(array $blocks): void
    {
        foreach ($blocks as $block) {
            $this->loadBlock($block);
        }
    }

    /**
     * Register a single block
     *
     * @param  array{ metaFile: string, phpFiles: string[] }  $block
     *
     * @return void
     */
    protected function loadBlock(array $block): void
    {
        if (! empty($block['phpFiles'])) {
            foreach ($block['phpFiles'] as $phpFile) {
                require_once $phpFile;
            }
        }
        \register_block_type($block['metaFile']);
    }

    /**
     * Creates or updates a cache file with all relevant block data
     *
     * @param  string  $cacheFile
     * @param  array{ array{ metaFile: string, phpFiles: string[] }  $blocksData
     *
     * @return void
     */
    protected function updateBlocksPathCacheFile(string $cacheFile, array $blocksData): void
    {
        \file_put_contents($cacheFile, "<?php return " . var_export($blocksData, true) . ";");
    }

    /**
     * Load blocks defined in a cache file
     *
     * @param  string  $cacheFile
     *
     * @return void
     */
    protected function loadBlocksPathCacheFile(string $cacheFile): void
    {
        $this->loadBlocks(require $cacheFile);
    }

    /**
     * Delete cache files in registered paths
     *
     * @return string[]
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
