<?php

namespace Morningtrain\WP\Blocks\Classes;

class Block
{
    private BlockMeta $blockMeta;

    public function __construct(protected string $dir)
    {
        if (file_exists(trailingslashit($dir) . "block.json")) {
            $this->blockMeta = new BlockMeta(json_decode(file_get_contents(trailingslashit($dir) . "block.json"),
                true));
        }
    }

    public function scriptDependencies(string|array $deps): static
    {
        $this->blockMeta->scriptDependencies = (array) $deps;

        return $this;
    }

    public function styleDependencies(string|array $deps): static
    {
        $this->blockMeta->styleDependencies = (array) $deps;

        return $this;
    }

    public function editorStyleDependencies(string|array $deps): static
    {
        $this->blockMeta->editorStyleDependencies = (array) $deps;

        return $this;
    }

    public function renderCallback(callable $callback): static
    {
        $this->blockMeta->renderCallback = $callback;

        return $this;
    }

    public function view(string $view): static
    {
        $this->blockMeta->view = $view;
        $this->blockMeta->renderCallback = [$this->blockMeta, 'view'];

        return $this;
    }

    public function register(): \WP_Block_Type|bool
    {
        if (! did_action('init')) {
            \add_action('init', [$this, 'register']);

            return false;
        }

        if (! empty($this->blockMeta->editorStyleDependencies)) {
            \add_action('enqueue_block_editor_assets', [$this->blockMeta, 'enqueueAdminDependencies']);
        }

        if (! empty($this->blockMeta->styleDependencies) || ! empty($this->blockMeta->scriptDependencies)) {
            \add_action('enqueue_block_assets', [$this->blockMeta, 'enqueueDependencies']);
        }

        return \register_block_type($this->dir, [
            'render_callback' => $this->blockMeta->renderCallback,
        ]);
    }
}
