<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Bnomei\Storybook;

test('singleton', function () {
    // create
    $storybook = Storybook::singleton();
    expect($storybook)->toBeInstanceOf(Storybook::class);

    // from static cached
    $storybook = Storybook::singleton();
    expect($storybook)->toBeInstanceOf(Storybook::class);
});
test('option', function () {
    $storybook = new Storybook([
        'debug' => true,
    ]);

    expect($storybook->option('debug'))->toBeTrue();

    expect($storybook->option())->toBeArray();
});
test('construct', function () {
    $storybook = new Storybook;
    expect($storybook)->toBeInstanceOf(Storybook::class);
});
