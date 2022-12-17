<?php

declare(strict_types=1);

if (!class_exists('Bnomei\Storybook')) {
    require_once __DIR__ . '/../classes/Storybook.php';
}

use Bnomei\Storybook;
use Kirby\CLI\CLI;
use Kirby\Filesystem\F;

return [
    'description' => 'Watch Snippet and Template files for changes and generate Storybook files',
    'args' => [
        'interval' => [
            'prefix'       => 'i',
            'longPrefix'   => 'interval',
            'description'  => 'Duration in milliseconds between file watcher checks',
            'defaultValue' => 1000,
            'castTo'       => 'int',
        ],
    ],
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->blue('Starting file watcher...');

        while (true) {
            $storybook = Storybook::singleton();
            $lastFile = '';
            try {
                foreach($storybook->snippets() as $key => $filepath) {
                    $lastFile = $filepath;
                    $storybook->generateStorybookFiles('snippets', $key, $filepath);
                }
                foreach($storybook->templates() as $key => $filepath) {
                    $lastFile = $filepath;
                    $storybook->generateStorybookFiles('templates', $key, $filepath);
                }
            } catch (Exception $exception) {
                // this will complain about every file not working with storybook
                // on every iteration of the file watcher
                if(option('bnomei.watcher.errors')) {
                    defined('STDOUT') && $cli->red($lastFile);
                    defined('STDOUT') && $cli->out($exception->getMessage());
                }
            }

            usleep($cli->arg('interval'));
        }
    }
];
