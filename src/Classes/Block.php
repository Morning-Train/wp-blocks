<?php

namespace Morningtrain\WP\Blocks\Classes;

class Block
{

    protected string $name;
    protected string $scriptHandle;
    protected string $styleHandle;
    protected string $editorStyleHandle;
    protected array $scriptDependencies = [];
    protected array $styleDependencies = [];
    protected array $editorStyleDependencies = [];

    protected string $buildDir;
    protected string $buildUrl;

    protected mixed $renderCallback = null; // Callable

    protected array $assetsFile;
    protected array $settings = [];

    // $view ?

    public function __construct(protected string $namespace)
    {
        [$group, $name] = explode('/', $this->namespace);
        $handle = str_replace('/', '-', $this->namespace);
        $this->name($name)
            ->scriptHandle($handle)
            ->styleHandle('style-' . $handle)
            ->editorStyleHandle($handle);
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function scriptHandle(string $handle): static
    {
        $this->scriptHandle = $handle;

        return $this;
    }

    public function styleHandle(string $handle): static
    {
        $this->styleHandle = $handle;

        return $this;
    }

    public function editorStyleHandle(string $handle): static
    {
        $this->editorStyleHandle = $handle;

        return $this;
    }

    public function buildDir(string $path): static
    {
        $this->buildDir = $path;

        return $this;
    }

    public function buildUrl(string $url): static
    {
        $this->buildUrl = $url;
        $this->buildUrl = $url;

        return $this;
    }

    public function scriptDependencies(string|array $deps): static
    {
        $this->scriptDependencies = (array) $deps;

        return $this;
    }

    public function styleDependencies(string|array $deps): static
    {
        $this->styleDependencies = (array) $deps;

        return $this;
    }

    public function editorStyleDependencies(string|array $deps): static
    {
        $this->editorStyleDependencies = (array) $deps;

        return $this;
    }

    public function renderCallback(callable $callback): static
    {
        $this->renderCallback = $callback;

        return $this;
    }

    public function settings(array $settings): static
    {
        $this->settings = $settings;

        return $this;
    }

    protected function loadAssetsFile(): static
    {
        $this->assetsFile = require trailingslashit($this->buildDir) . "{$this->name}.asset.php";

        return $this;
    }

    protected function registerScript(): static
    {
        \wp_register_script(
            $this->scriptHandle,
            trailingslashit($this->buildUrl) . "{$this->name}.js",
            array_merge($this->scriptDependencies, $this->assetsFile['dependencies']),
            $this->assetsFile['version']
        );

        return $this;
    }

    protected function registerStyle(): static
    {
        \wp_register_style(
            $this->styleHandle,
            trailingslashit($this->buildUrl) . "style-{$this->name}.css",
            $this->styleDependencies,
            $this->assetsFile['version']
        );

        return $this;
    }

    protected function registerEditorStyle(): static
    {
        \wp_register_style(
            $this->editorStyleHandle,
            trailingslashit($this->buildUrl) . "{$this->name}.css",
            $this->editorStyleDependencies,
            $this->assetsFile['version']
        );

        return $this;
    }

    public function register(): \WP_Block_Type|bool
    {
        if (! did_action('init')) {
            \add_action('init', [$this, 'register']);

            return false;
        }

        $this->loadAssetsFile()
            ->registerScript()
            ->registerStyle()
            ->registerEditorStyle();

        return \register_block_type($this->namespace,
            array_merge(
                [
                    'editor_script' => $this->scriptHandle,
                    'style' => $this->styleHandle,
                    'editor_style' => $this->editorStyleHandle,
                    //'render_callback' => $this->renderCallback,
                ],
                $this->settings
            ),
        );
    }
}
