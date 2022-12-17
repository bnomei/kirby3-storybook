<?php

declare(strict_types=1);

if (!class_exists('Bnomei\Storybook')) {
    require_once __DIR__ . '/../classes/Storybook.php';
}

use Bnomei\Storybook;
use Kirby\CLI\CLI;

return [
    'description' => 'Watch Snippet and Template files for changes and generate Storybook files',
    'args' => [
        'interval' => [
            'prefix'       => 'i',
            'longPrefix'   => 'interval',
            'description'  => 'Duration in milliseconds between file watcher checks',
            'defaultValue' => 10000,
            'castTo'       => 'int',
        ],
    ],
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->blue('Starting file watcher...');

        while (true) {
            $storybook = Storybook::singleton();

            foreach ($storybook->snippets() as $key => $filepath) {
                try {
                    $storybook->generateStorybookFiles('snippets', $key, $filepath);
                } catch (Exception $exception) {
                    if (option('bnomei.storybook.errors')) {
                        defined('STDOUT') && $cli->red($filepath);
                        defined('STDOUT') && $cli->out($exception->getMessage());
                    }
                }
            }

            foreach ($storybook->templates() as $key => $filepath) {
                try {
                    $storybook->generateStorybookFiles('templates', $key, $filepath);
                } catch (Exception $exception) {
                    if (option('bnomei.storybook.watcher.errors')) {
                        defined('STDOUT') && $cli->red($filepath);
                        defined('STDOUT') && $cli->out($exception->getMessage());
                    }
                }
            }

            usleep($cli->arg('interval') * 1000);
        }
    }
];
