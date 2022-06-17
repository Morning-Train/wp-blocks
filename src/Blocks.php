<?php

    namespace Morningtrain\WP\Blocks;

    use Morningtrain\PHPLoader\Loader;

    class Blocks
    {
        public static function loadDir(string|array $path)
        {
            Loader::create($path)
                ->isA(Abstracts\AbstractBlock::class)
                ->invoke();
        }
    }