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
            'defaultValue' => 1000,
            'castTo'       => 'int',
        ],
        'pattern' => [
            'prefix'       => 'p',
            'longPrefix'   => 'pattern',
            'description'  => 'File name regex pattern, like \'/.*article.php/\' or exact match like \'article\'',
            'castTo'       => 'string',
        ],
        'once' => [
            'longPrefix'   => 'once',
            'description'  => 'run command only once',
            'noValue'      => true,
        ],
        'errors' => [
            'longPrefix'   => 'errors',
            'description'  => 'show errors',
            'noValue'      => true,
        ],
    ],
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->blue('Starting file watcher...');

        $root = $cli->kirby()->root();
        $pattern = $cli->arg('pattern');
        $storybook = Storybook::singleton([
            'cli' => true, // enforce cli mode even if called from janitor
            'watcher_errors' => $cli->arg('errors'),
        ]);

        while (true) {
            $time = time();
            $updated = 0;
            $count = 0;

            foreach ($storybook->snippets() as $key => $filepath) {
                if ($storybook->pattern($filepath, $pattern) === false) {
                    continue;
                }
                $count++;
                try {
                    if ($storybook->modified($filepath) && $storybook->generateStorybookFiles('snippets', $key, $filepath)) {
                        defined('STDOUT') && $cli->out('📖 ' . str_replace($root, '', $filepath));
                        $updated++;
                    }
                } catch (Exception $exception) {
                    if ($storybook->option('watcher_errors')) {
                        defined('STDOUT') && $cli->red('❌  ' . str_replace($root, '', $filepath));
                        defined('STDOUT') && $cli->out(" ↪ " . $exception->getMessage());
                    }
                }
            }

            foreach ($storybook->templates() as $key => $filepath) {
                if ($storybook->pattern($filepath, $pattern) === false) {
                    continue;
                }
                $count++;
                try {
                    if ($storybook->modified($filepath) && $storybook->generateStorybookFiles('templates', $key, $filepath)) {
                        defined('STDOUT') && $cli->out('📖 ' . str_replace($root, '', $filepath));
                        $updated++;
                    }
                } catch (Exception $exception) {
                    if ($storybook->option('watcher_errors')) {
                        defined('STDOUT') && $cli->red('❌  ' . str_replace($root, '', $filepath));
                        defined('STDOUT') && $cli->out(" ↪ " . $exception->getMessage());
                    }
                }
            }

            if ($cli->arg('once')) {
                $data = [
                    'status' => $updated > 0 ? 200 : 204,
                    'duration' => time() - $time,
                    'updated' => $updated,
                    'count' => $count,
                    'message' => $updated . ' stories generated',
                ];

                defined('STDOUT') && $cli->blue($data['duration'] . ' sec');
                defined('STDOUT') && $cli->blue($data['count'] . ' files watched');
                defined('STDOUT') && $cli->success($data['message']);

                function_exists('janitor') && janitor()->data($cli->arg('command'), $data);

                break;
            }

            usleep($cli->arg('interval') * 1000);
        }
    }
];
