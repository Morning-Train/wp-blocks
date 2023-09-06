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
Blocks::setup(__DIR__ . "/public/build/blocks");

// To add an additional directory
Blocks::registerBlockDirectory(__DIR__ . "/public/build/blocks");
```

## Using a View

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

## Credits

- [Mathias Munk](https://github.com/mrmoeg)
- [All Contributors](../../contributors)

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
