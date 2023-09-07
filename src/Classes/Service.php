<?php

namespace Morningtrain\WP\Blocks\Classes;

use Morningtrain\WP\View\View;

class Service
{
    /**
     * Initialize class
     *
     * @return void
     */
    public function init(): void
    {
        \add_filter('block_type_metadata_settings', [$this, 'allowViewRenderInBlockMeta'], 99, 2);
    }

    /**
     * Handle custom block meta property "renderView"
     *
     * @param  array  $settings
     * @param  array  $metadata
     * @return array
     * @see https://developer.wordpress.org/reference/hooks/block_type_metadata_settings/
     */
    public function allowViewRenderInBlockMeta(array $settings, array $metadata): array
    {
        if (! class_exists("\Morningtrain\WP\View\View")) {
            return $settings;
        }

        if (! isset($metadata['renderView'])) {
            return $settings;
        }

        $settings['render_callback'] = static function ($attributes, $content, $block) use ($metadata) {
            return View::render($metadata['renderView'], [
                'attributes' => $attributes,
                'content' => $content,
                'block' => $block,
                'blockProps' => \get_block_wrapper_attributes(),
            ]);
        };

        return $settings;
    }
}
