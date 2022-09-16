# Morningtrain\WP\Blocks

A Morningtrain package for working with WordPress Gutenberg blocks more easily.

## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
    - [Installation](#installation)
- [Dependencies](#dependencies)
    - [morningtrain/php-loader](#morningtrainphp-loader)
- [Usage](#usage)
    - [Loading the block directory](#loading-the-block-directory)
    - [Registering a block](#registering-a-block)
    - [Registering a block pattern](#registering-a-block-pattern)
      - [Loading patterns from a custom directory](#loading-patterns-from-a-custom-directory)
      - [Specifying the pattern itself](#specifying-the-pattern-itself)
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
- Register block patterns from template HTML files using a fluid api

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
Blocks::setup(__DIR__ . "/resources/blocks",__DIR__ . "/public/build/blocks");
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

### Registering a block pattern

You can easily register a
new [block pattern](https://developer.wordpress.org/reference/functions/register_block_pattern/) into WordPress using
the `Blocks::pattern` method.

This example will register a block pattern into the "morningtrain" namespace called "product-page". When no title is
supplied the name will be parsed automatically. When no template file to use is supplied Pattern will look in the _
patterns directory in the blocks main dir for a .html file matching the pattern name.

In this example the title would be "Product Page" and the template file used would be blocks/_
patterns/product-page.html.

```php
\Morningtrain\WP\Blocks\Blocks::pattern('morningtrain','product-page');
```

Here is a more fleshed-out example:

```php
\Morningtrain\WP\Blocks\Blocks::pattern('morningtrain','product-page')
    ->title(__('Product Page','morningtrain'))
    ->description(__('Basic template for Product Pages','morningtrain'))
    ->categories(['featured','morningtrain','products'])
    ->keywords(['morningtrain','product','fullpage'])
    ->usePattern('product-page.html');
```

**Note:** All public methods on the Pattern class have usefull descriptions and the properties match the ones seen in
the codex, which is also referenced on the class itself

#### Loading patterns from a custom directory

As a default the package will look for a `/_patterns/` directory in the blocks dir.

If your patterns are located elsewhere you can define it like so:

```php
\Morningtrain\WP\Blocks\Blocks::setPatternDirectory(__DIR__ . '/my-patterns');
```

#### Specifying the pattern itself

By pattern file in the _patterns dir:

```php
\Morningtrain\WP\Blocks\Blocks::pattern('morningtrain','product-page')
    ->usePattern('product-page.html');
```

By template part file in the /parts/ dir:

```php
\Morningtrain\WP\Blocks\Blocks::pattern('morningtrain','product-page')
    ->useTemplatePart('product-page.html');
```

By template file in any dir:

```php
\Morningtrain\WP\Blocks\Blocks::pattern('morningtrain','product-page')
    ->useFile(__DIR__ . '/product-page.html');
```

As a string:

```php
\Morningtrain\WP\Blocks\Blocks::pattern('morningtrain','product-page')
    ->useString('<div>Products ... </div>');
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
