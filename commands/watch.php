<?php

declare(strict_types=1);

if (!class_exists('Bnomei\Storybook')) {
    require_once __DIR__ . '/../classes/Storybook.php';
}

use Bnomei\Storybook;
use Kirby\CLI\CLI;
use Kirby\Filesystem\F;
use Symfony\Component\Finder\Finder;

return [
    'description' => 'Watch files and folders',
    'args' => [],
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->blue('ok');

        while (true) {
            sleep(1);

            F::write(
                kirby()->roots()->index() . '/stories/demo.html',
                snippet('demo', return: true)
            );
        }
    }
];
