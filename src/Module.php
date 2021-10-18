<?php


namespace Morningtrain\WP\Blocks;


use Morningtrain\WP\Core\Classes\ClassLoader;
use Symfony\Component\Finder\Finder;

class Module extends \Morningtrain\WP\Core\Abstracts\AbstractModule
{

    public function init()
    {
        parent::init();

        $this->loadACFBlocks();
    }

    /**
     * Loads and registers all project ACF Blocks
     */
    public function loadACFBlocks(): void
    {
        if(!function_exists('get_field')){
            return;
        }

        $path = $this->project_context->getNamedDir('blocks');
        if ($path === null) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Block.php')->in($this->project_context->getBaseDir() . $path);

        if (!$finder->hasResults()) {
            return;
        }

        $block_files = [];
        foreach ($finder as $file) {
            $block_files[] = $file->getRealPath();
        }

        ClassLoader::requireAndCall($block_files, 'init', $this, '\Morningtrain\WP\Blocks\Abstracts\AbstractBlock');
    }
}