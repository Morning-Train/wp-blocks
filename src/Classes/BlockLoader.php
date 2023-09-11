<?php

namespace Morningtrain\WP\Blocks\Classes;

use Morningtrain\WP\Blocks\Blocks;
use Symfony\Component\Finder\Finder;

class BlockLoader
{
    protected string $cacheFilename = '_cache.php';
    protected array $blockPaths = [];
    protected array $fileDependencyProperties = ['phpScript', 'viewPhpScript', 'editorPhpScript'];

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
            $this->loadBlocksFromCacheFile($cacheFile);

            return;
        }

        $blocks = [];
        $metaFiles = $this->locateBlocksInPath($path);
        foreach ($metaFiles as $metaFile) {
            $blocks[] = $this->resolveDataByMetaFile($metaFile);
        }

        if (\wp_get_environment_type() === 'production') {
            $this->updateBlocksCacheFile($cacheFile, $blocks);
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
     * Resolve necessary block data
     *
     * @param  string  $metaFile
     * @return string[] The data to cache
     */
    protected function resolveDataByMetaFile(string $metaFile): array
    {
        $data = [
            'metaFile' => $metaFile,
        ];
        $metaData = \wp_json_file_decode($metaFile, ['associative' => true]);

        foreach ($this->fileDependencyProperties as $fileDependencyProperty) {
            $deps = $this->resolveFileDependencyProperty($fileDependencyProperty, $metaData);
            if (! empty($deps)) {
                $data[$fileDependencyProperty] = $deps;
            }
        }

        return $data;
    }

    /**
     * Resolve a list of file dependencies in the block.json file as a list of relative paths
     *
     * @param  string  $property  The property in block.json
     * @param  array  $metaData  The full metadata object
     * @return array A list of relative paths
     */
    protected function resolveFileDependencyProperty(string $property, array $metaData): array
    {
        if (empty($metaData[$property])) {
            return [];
        }

        $dependencies = [];
        foreach ((array) $metaData[$property] as $dependency) {
            $path = \remove_block_asset_path_prefix($dependency);
            if ($dependency !== $path) {
                $dependencies[] = $path;
            }
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
     * Register a single block and load its dependencies
     *
     * @param  array{ metaFile: string, phpFiles: string[] }  $block
     *
     * @return void
     */
    protected function loadBlock(array $block): void
    {
        $this->loadBlockDependencies($block);

        \register_block_type($block['metaFile']);
    }

    /**
     * Load block dependencies
     *
     * @param  array  $block
     * @return void
     */
    protected function loadBlockDependencies(array $block): void
    {
        $dir = dirname($block['metaFile']);
        $isJsonRequest = \wp_is_json_request();
        $deps = [];

        if (isset($block['phpScript'])) {
            $deps = $block['phpScript'];
        }

        if (isset($block['editorPhpScript']) && ($isJsonRequest || \wp_should_load_block_editor_scripts_and_styles())) {
            $deps = array_merge($deps, $block['editorPhpScript']);
        }

        if (isset($block['viewPhpScript']) && (! $isJsonRequest && ! \is_admin())) {
            $deps = array_merge($deps, $block['viewPhpScript']);
        }

        foreach ($deps as $dep) {
            require $dir . "/" . $dep;
        }
    }

    /**
     * Creates or updates a cache file with all relevant block data
     *
     * @param  string  $cacheFile
     * @param  array{ array{ metaFile: string, phpFiles: string[] }  $blocksData
     *
     * @return void
     */
    protected
    function updateBlocksCacheFile(
        string $cacheFile,
        array $blocksData
    ): void {
        \file_put_contents($cacheFile, "<?php return " . var_export($blocksData, true) . ";");
    }

    /**
     * Load blocks defined in a cache file
     *
     * @param  string  $cacheFile
     *
     * @return void
     */
    protected
    function loadBlocksFromCacheFile(
        string $cacheFile
    ): void {
        $this->loadBlocks(require $cacheFile);
    }

    /**
     * Delete cache files in registered paths
     *
     * @return string[]
     */
    public
    function deleteCacheFiles(): array
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
