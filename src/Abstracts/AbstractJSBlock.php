<?php


namespace Morningtrain\WP\Blocks\Abstracts;


abstract class AbstractJSBlock extends \Morningtrain\WP\Core\Abstracts\AbstractModule
{
    protected array $js_dependencies = [];
    protected array $css_dependencies = [];
    protected array $block_settings = [];
    protected array $assets_file_contents = [];

    public function init()
    {
        parent::init();

        $this->assets_file_contents = require($this->getAssetFile());

        if (did_action('init') > 0) {
            $this->register();
        } else {
            \add_action('init', [$this, 'register']);
        }
    }

    public function register()
    {
        $this->registerBlockScript();
        $this->registerBlockEditorStyle();
        $this->registerBlockStyle();
        $this->registerBlock();
    }

    public function getBlockBuildDir(): string
    {
        if ($this->hasProjectContext()) {
            return $this->project_context->getBaseDir() . $this->project_context->getNamedDir('build') . "/blocks";
        } else {
            return $this->getBaseDir() . $this->getNamedDir('build') . "/blocks";
        }
    }

    public function getBlockBuildUrl(): string
    {
        if ($this->hasProjectContext()) {
            return $this->project_context->getBaseUrl() . $this->project_context->getNamedDir('build') . "/blocks";
        } else {
            return $this->getBaseUrl() . $this->getNamedDir('build') . "/blocks";
        }
    }

    public function getAssetFile(): string
    {
        return $this->getBlockBuildDir() . "/{$this->getName()}.asset.php";
    }


    public function getScriptFileUrl(): string
    {
        return $this->getBlockBuildUrl() . "/{$this->getName()}.js";
    }

    public function getStyleFileUrl(): string
    {
        return $this->getBlockBuildUrl() . "/style-{$this->getName()}.css";
    }

    public function getEditorStyleFileUrl(): string
    {
        return $this->getBlockBuildUrl() . "/{$this->getName()}.css";
    }

    public function getScriptHandle(): string
    {
        return mb_strtolower($this->getName());
    }

    public function getStyleHandle(): string
    {
        return mb_strtolower($this->getName());
    }

    public function getEditorStyleHandle(): string
    {
        return "editor-" . mb_strtolower($this->getName());
    }

    protected function registerBlockScript(): bool
    {
        return \wp_register_script(
            $this->getScriptHandle(),
            $this->getScriptFileUrl(),
            array_merge($this->js_dependencies, $this->assets_file_contents['dependencies']),
            $this->assets_file_contents['version']
        );
    }

    protected function registerBlockStyle(): bool
    {
        return \wp_register_style(
            $this->getStyleHandle(),
            $this->getStyleFileUrl(),
            $this->css_dependencies,
            $this->assets_file_contents['version']
        );
    }

    protected function registerBlockEditorStyle(): bool
    {
        return \wp_register_style(
            $this->getEditorStyleHandle(),
            $this->getEditorStyleFileUrl(),
            $this->css_dependencies,
            $this->assets_file_contents['version']
        );
    }

    protected function registerBlock()
    {
        $register_args = array_merge(
            $this->block_settings,
            array(
                'editor_script' => $this->getScriptHandle(),
                'editor_style' => $this->getEditorStyleHandle(),
                'style' => $this->getStyleHandle(),
            )
        );

        $lc_name = mb_strtolower($this->getName());
        $package_name = "morningtrain/{$lc_name}";
        \register_block_type($package_name, $register_args);
    }
}