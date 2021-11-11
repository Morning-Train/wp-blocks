# Morningtrain\WP\Blocks

A Morningtrain package for working with WordPress Gutenberg blocks more easily.

## Table of contents

* [About Morningtrain WP Blocks](#about-morningtrain-wp-blocks)
* [Installation](#installation)
* [Examples](#examples)
    * [JS Block](#js-block)
        * [Basic Block Example](#basic-block-example)
    * [ACF Block](#acf-block)
* [Features](#features)
* [Dependencies](#dependencies)

## About Morningtrain WP Blocks

Morningtrain WP Blocks sets a structure for your projects blocks, defines a few helper classes to help you get started
and lets you load your blocks easily without having to register all them yourselves.

## Installation

Firstly require and install this package `composer install morningtrain/wp-blocks`

Then wherever you `init()` your project you can now initialize Blocks as a
module: `$theme->addModule(new \Morningtrain\WP\Blocks\Module());`

In the root of your project create a directory called "Blocks". This is where your blocks will be created!

Note the uppercase directory name. This is because your blocks will need to follow PSR-4 since a PHP class will be your
first block file. Yes, even though your entire block will be written in javascript.

This file will need to extend either `\Morningtrain\WP\Blocks\Abstracts\AbstractJSBlock`
or `\Morningtrain\WP\Blocks\Abstracts\AbstractACFBlock`. These abstracts will handle most of the setup work for you!

Read through them if you are curious about their capabilities.

The names of these files MUST end with `*Block.php` so that the loader can find them.

### NPM & Webpack

Make sure that `@wordpress/scripts` is installed and any block editor script you might need.

* 🚧THE WEBPACK CONFIG IS NOT YET AVAILABLE AS A PACKAGE*🚧

## Features

* Let's you write standard WP Gutenberg blocks AND ACF blocks!
* Loads your blocks automatically. You don't need to spend your valuable time registering everything by yourself

## Examples

### JS Block
*NOTE: the namespace of your blocks MUST be **morningtrain***

#### Basic Block Example

```php
// Blocks/BasicExample/BasicExampleBlock.php
<?php


namespace MyProject\Blocks\BasicExample;


class BasicExampleBlock extends \Morningtrain\WP\Blocks\Abstracts\AbstractJSBlock
{
    // You don't need to do anything here if your block is kinda basic
}
```

```js  
// Blocks/BasicExample/index.js
import { registerBlockType } from '@wordpress/blocks'
import { __ } from '@wordpress/i18n'
import './style.scss'
import './editor.scss'

registerBlockType('morningtrain/basicexample', {
  title: __('Basic Example Block', 'textdomain'),
  description: __('A simple rich text example', 'textdomain'),
  attributes: {
    text: {
      type: 'string',
      source: 'html',
      selector: '.text',
    }
  },
  edit ({ className, attributes, setAttributes, clientId }) {
    return (
      <div className={className}>
        <RichText
          tagName="p"
          value={attributes.text}
          onChange={(text) => setAttributes({ text: text })}
          placeholder={__('Write some text here', 'textdomain')}
          className={'text'}
        />
      </div>
    )
  },
  save ({ className, attributes, setAttributes, clientId }) {
    return (
      <div className={className}>
        <RichText.Content tagName={'p'} value={attributes.text} className={'text'}/>
      </div>
    )
  }
})
```

```scss
//  Blocks/BasicExample/style.scss
.exampleblock {
  color: magenta;
}
```

```scss
//  Blocks/BasicExample/editor.scss
.exampleblock {
  color: magenta;

  &:hover {
    cursor: pointer;
    border: 1px solid blue;
  }
}
```

### ACF Block

## Dependencies

This package requires `Morningtrain\WP\Core`