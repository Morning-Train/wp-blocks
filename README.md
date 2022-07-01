# Morningtrain\WP\Blocks

A Morningtrain package for working with WordPress Gutenberg blocks more easily.

## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
    - [Installation](#installation)
- [Dependencies](#dependencies)
    - [morningtrain/php-loader](#morningtrainphp-loader)
- [Usage](#usage)
  -[Loading the block directory](#loading-the-block-directory)
  -[Registering a block](#registering-a-block)
- [Credits](#credits)
- [Testing](#testing)
- [License](#license)


## Introduction

This tool is made for organizing WordPress Gutenberg blocks!

This tool lets you:

- Load all blocks found in a directory
- Register blocks using a fluid api
- Render Blade views directly as render_callback for your block
- Set script and stylesheet dependencies for your block

## Getting Started

To get started install the package as described below in [Installation](#installation).

To use the tool have a look at [Usage](#usage)

### Installation

Install with composer

```bash
composer require morningtrain/wp-blocks
```

## Dependencies

### morningtrain/php-loader

[PHP Loader](https://github.com/Morning-Train/php-loader) is used to load and initialize all Hooks

## Usage

### Loading the block directory

```php
use Morningtrain\WP\Blocks\Blocks;
// Tell Blocks where the built/compiled files are located
Blocks::setBuildDir(__DIR__ . "/public/build/blocks");
Blocks::setBuildUrl(get_stylesheet_directory_uri() . "/public/build/blocks");
```

### Registering a block

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

## Credits

- [Mathias Munk](https://github.com/mrmoeg)
- [All Contributors](../../contributors)

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
