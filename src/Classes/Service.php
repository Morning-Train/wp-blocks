<?php

namespace Morningtrain\WP\Blocks\Classes;

use Morningtrain\WP\View\View;

class Service
{
    public function init(): void
    {
        \add_filter('block_type_metadata_settings', [$this, 'allowViewRenderInBlockMeta'], 99, 2);
    }

    public function allowViewRenderInBlockMeta($settings, $metadata)
    {
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
