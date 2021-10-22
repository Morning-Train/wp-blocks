<?php


namespace Morningtrain\WP\Blocks;


class Module extends \Morningtrain\WP\Core\Abstracts\AbstractModule
{

    public function init()
    {
        parent::init();
        $this->loadBlocks();
    }

    /**
     * Loads and registers all project ACF Blocks
     */
    public function loadBlocks(): void
    {
        $path = $this->project_context->getNamedDir('blocks');
        BlocksLoader::registerBlocksInDir($this->project_context->getBaseDir() . $path,$this->project_context);
    }
}