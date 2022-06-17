<?php

namespace Morningtrain\WP\Blocks\Abstracts;

abstract class AbstractBlock
{
    protected string $namespace;

    public static function register()
    {
        (new static())();
    }

    public function __invoke()
    {

    }
}



/*Block::create('test/cover')
    ->dependsOnScript('jquery')
    ->useStyle('cover-style')
    ->publicDir(__DIR__."/build")
    ->register();*/
