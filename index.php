<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/storybook', [
    'options' => [
        'cli' => fn () => php_sapi_name() === 'cli',
        'folder' => function (): ?string {
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->in(kirby()->root('index'))
                ->notPath('/.*node_modules.*/')
                ->name('stories');
            foreach ($finder->directories() as $dir) {
                return $dir->getRealPath();
            }
            return null;
        },
        'stories' => [
            'json' => fn () => class_exists('Kirby\Kql\Kql'),
            'yml' => true,
        ],
    ],
    'commands' => [ // https://github.com/getkirby/cli
        'storybook:watch' => require __DIR__ . '/commands/watch.php',
    ],
    'components' => [
        'snippet' => function (\Kirby\Cms\App $kirby, $name, array $data = [], bool $slots = false) {
            // support other plugins if installed
            // https://github.com/lukaskleinschmidt/kirby-snippet-controller
            if(function_exists('snippet_controller')) {
                $data = snippet_controller($name, $data);
            }

            // merge data with...
            $data = \Bnomei\Storybook::singleton()->loadData(
                $data,
                $name
            );

            return $kirby->core()->components()['snippet'](
                $kirby,
                $name,
                $data,
                $slots,
            );
        }
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