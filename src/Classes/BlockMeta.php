<?php

namespace Morningtrain\WP\Blocks\Classes;

use Mockery\Exception;

class BlockMeta
{
    public array $editorStyleDependencies = [];
    public mixed $renderCallback = null;
    public mixed $view = null;
    public array $scriptDependencies = [];
    public array $styleDependencies = [];

    public function __construct(protected ?array $blockMeta)
    {
    }

    public function enqueueDependencies()
    {
        if (! has_block($this->blockMeta['name'])) {
            return;
        }
        foreach ($this->styleDependencies as $dep) {
            \wp_enqueue_style($dep);
        }
        foreach ($this->scriptDependencies as $dep) {
            \wp_enqueue_script($dep);
        }
    }

    public function enqueueAdminDependencies()
    {
        if (! has_block($this->blockMeta['name'])) {
            return;
        }
        foreach ($this->editorStyleDependencies as $dep) {
            \wp_enqueue_style($dep);
        }
    }

    public function view(?array $blockAttributes)
    {
        // Make sure View Class exists
        if (! class_exists('Morningtrain\WP\View\View')) {
            throw new Exception('Can\'t render views. View class does not exist');
        }
        //render view
        return \Morningtrain\WP\View\View::render($this->view, $blockAttributes);
    }
}
