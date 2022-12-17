<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/storybook', [
    'options' => [
        'cli' => fn() => php_sapi_name() === 'cli',
        'stories' => [
            'json' => fn() => class_exists('Kirby\Kql\Kql'),
            'yml' => true,
        ],
        'watcher' => [
            'errors' => false,
        ]
    ],
    'commands' => [ // https://github.com/getkirby/cli
        'storybook:watch' => require __DIR__ . '/commands/watch.php',
    ],
]);

if (!class_exists('Bnomei\Storybook')) {
    require_once __DIR__ . '/classes/Storybook.php';
}

if (!function_exists('storybook')) {
    function storybook(array $csf = [], ?string $filepath = null): array
    {
        $backfiles = debug_backtrace();
        $filepath ??= $backfiles[0]['file'];

        return \Bnomei\Storybook::singleton()->csf($csf, $filepath);
    }
}
