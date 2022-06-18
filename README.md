# Morningtrain\WP\Blocks

A Morningtrain package for working with WordPress Gutenberg blocks more easily.

## Table of contents

```php
use Morningtrain\WP\Blocks\Blocks;
// Tell Blocks where the built/compiled files are located
Blocks::setBuildDir(__DIR__ . "/public/build/blocks");
Blocks::setBuildUrl(get_stylesheet_directory_uri() . "/public/build/blocks");
```

```php
// Basic Block registration
use Morningtrain\WP\Blocks\Blocks;
Blocks::create('acme/block') // Now block name will be block and .js file should be "block.js"
    ->register();
```

```php
// Advanced Block registration
use Morningtrain\WP\Blocks\Blocks;
Blocks::create('acme/block')
    ->name('block') // Defaults to the second part of namespace
    ->buildDir(__DIR__ . "/build") // Will be supplied by Blocks if set
    ->buildUrl(get_stylesheet_directory_uri() . "/build") // Will be supplied by Blocks if set
    ->scriptDependencies(['some-script']) // If your script should depend on another script such as jQuery or Swiper
    ->styleDependencies(['some-style']) // Same as above for style
    ->editorStyleDependencies(['some-editor-style']) // Same as above for editor
    ->scriptHandle('acme-block') // Defaults to namespace but with "-" instead of "-" 
    ->styleHandle('acme-block') // same as above
    ->editorStyleHandle('editor-acme-block') // same as above but prefixed !editor"
    ->settings([ // Additional settings. See https://developer.wordpress.org/reference/functions/register_block_type/ $args
        'title' => 'My Cool Block Title'    
    ])
    ->renderCallback([MyClass::class,'renderBlock']) // A callback for server side rendering / dynamic blocks
    ->register(); // Register!!
```
