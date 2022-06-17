<?php

use Brain\Monkey;

beforeAll(function () {
    Monkey\setUp();
});

afterAll(function () {
    Monkey\tearDown();
});

it('has Block main class', function () {
    expect(\Morningtrain\WP\Blocks\Blocks::class)->toBeString();
});
