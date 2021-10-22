<?php

namespace Morningtrain\WP\Blocks;


use Morningtrain\WP\Core\Abstracts\AbstractModule;
use Morningtrain\WP\Core\Abstracts\AbstractProject;
use Morningtrain\WP\Core\Classes\ClassLoader;
use Symfony\Component\Finder\Finder;

class BlocksLoader
{

    /**
     * Register all ACF Blocks in a directory.
     *
     * @param string $dir_path
     */
    public static function registerBlocksInDir(string $dir_path, ?AbstractProject $project_context = null)
    {
        if (!is_dir($dir_path)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Block.php')->in($dir_path);

        if (!$finder->hasResults()) {
            return;
        }

        $block_files = [];
        foreach ($finder as $file) {
            $block_files[] = $file->getRealPath();
        }

        $blocks = ClassLoader::requireAndReturn($block_files,);

        foreach ($blocks as $block_class){
            /**
             * @var AbstractModule $block
             */
            $block = new $block_class();
            $block->setProjectContext($project_context);
            $block->init();
        }
    }
}