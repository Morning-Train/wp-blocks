<?php


    namespace Morningtrain\WP\Blocks\Abstracts;


    abstract class AbstractBlock_OLD
    {
        protected array $js_dependencies = [];
        protected array $css_dependencies = [];
        protected array $block_settings = [];
        protected array $assets_file_contents = [];

        public function __invoke()
        {
            $assets_file = $this->getAssetFile();

            if ($assets_file === null) {
                // Block does not seem to have been built
                // TODO: We might want to throw some kind of warning here
                return;
            }

            $this->assets_file_contents = require($assets_file);

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

        }

        public function getBlockBuildUrl(): string
        {

        }

        public function getAssetFile(): ?string
        {
            $file = $this->getBlockBuildDir() . "/{$this->getName()}.asset.php";
            return file_exists($file) ? $file : null;
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

            if (method_exists($this, 'renderCallback')) {
                $register_args['render_callback'] = [$this, 'renderCallback'];
            }

            $lc_name = mb_strtolower($this->getName());
            $package_name = "morningtrain/{$lc_name}";
            \register_block_type($package_name, $register_args);
        }
    }
