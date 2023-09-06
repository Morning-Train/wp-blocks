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
        $cacheFile = $path . "/_cache.php";
        if (\wp_get_environment_type() === 'production' && file_exists($cacheFile)) {
            $this->loadBlocksPathCacheFile($cacheFile);

            return;
        }

        $blocksData = [];

        $finder = new Finder();
        $finder->files()->name('block.json')->in($path);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $blocksData[$file->getRealPath()] = $this->loadBlock($file->getRealPath());
            }
        }
        if (\wp_get_environment_type() === 'production') {
            $this->updateBlocksPathCacheFile($cacheFile, $blocksData);
        }
    }

    protected function loadBlock(string $blockMetaFile): array
    {
        $dir = dirname($blockMetaFile);
        $phpFiles = [];

        $finder = new Finder();
        $finder->files()->name('*.php')->notName('*.asset.php')->in($dir)->depth('== 0');
        foreach ($finder as $file) {
            $phpFiles[] = $file->getRealPath();
            require_once $file->getRealPath();
        }

        \register_block_type($blockMetaFile);

        return $phpFiles;
    }

    protected function updateBlocksPathCacheFile($cacheFile, $blocksData)
    {
        \file_put_contents($cacheFile, "<?php return " . var_export($blocksData, true) . ";");
    }

    protected function loadBlocksPathCacheFile($cacheFile)
    {
        foreach (require $cacheFile as $blockMetaFile => $phpDeps) {
            \register_block_type($blockMetaFile);
            foreach ($phpDeps as $phpDep) {
                require_once $phpDep;
            }
        }
    }

    public function deleteCacheFiles()
    {
        $deletedFiles = [];
        foreach ($this->blockPaths as $blockPath) {
            if (file_exists($blockPath . "/_cache.php")) {
                unlink($blockPath . "/_cache.php");
                $deletedFiles[] = $blockPath . "/_cache.php";
            }
        }

        return $deletedFiles;
    }
}
