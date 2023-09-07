# Morningtrain\WP\Blocks

A Morningtrain package for working with WordPress blocks more easily.

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
- Render Blade views by defining them as `renderView` in block meta
- Load PHP dependencies by placing `*.php` files next to the `block.json` files

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

To initialize the package and/or to load blocks from a path use `Blocks::setup`

```php
use Morningtrain\WP\Blocks\Blocks;
// Tell Blocks where the built/compiled files are located
Blocks::setup(__DIR__ . "/public/build/blocks");

// To add another directory
Blocks::registerBlockDirectory(__DIR__ . "/public/build/blocks");
```

## Using a View

To serverside render a block using a Blade View set the custom `renderView` property.

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "foo/bar",
    "version": "0.1.0",
    "title": "Bar",
    "textdomain": "foo",
    "editorScript": "file:./index.js",
    "editorStyle": "file:./index.css",
    "style": "file:./style-index.css",
    "renderView": "my-view"
}
```

The view will have the following vars: `$block`, `$attributes`, `$content` and `$blockProps`

Example:

```
<div {!! $blockProps !!}>
    <h2>{{$attributes['title']}}</h2>
    <div>{!! $content !!}</div>
</div>
```

If you wish to view compose you may create a `*.php` file within your block folder.
As long as it is a sibling to the `block.json` file and is not named `*.asset.php` then it will automatically be loaded.

## Caching

If your environment is `production` then a cache containing all block files and their dependencies will be generated and
used so that the server doesn't have to look for them on every request.

To clear this cache you can use the CLI command:
```sh
wp wp-blocks deleteCacheFiles
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
