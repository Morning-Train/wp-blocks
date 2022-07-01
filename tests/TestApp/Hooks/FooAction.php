<?php

namespace TestApp\Hooks;

class FooAction extends \Morningtrain\WP\Hooks\Abstracts\AbstractActionHook
{
    protected string|array $hook = 'foo';
    public static bool $foo = false;

    public function handle()
    {
        static::$foo = true;
    }

}
