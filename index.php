<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/storybook', [
    'options' => [
        'cli' => fn () => php_sapi_name() === 'cli',
        'folder' => function (): ?string {
            foreach([
                        kirby()->root('index') . '/stories',
                        kirby()->root('index') . '/../stories'
                    ] as $dir) {
                if (Dir::exists($dir)) {
                    return $dir;
                }
            }
            
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
            'ignore' => [],
        ],
    ],
    'commands' => [ // https://github.com/getkirby/cli
        'storybook:watch' => require __DIR__ . '/commands/watch.php',
    ],
    'components' => [
        'snippet' => function (\Kirby\Cms\App $kirby, $name, array $data = [], bool $slots = false) {
            // support other plugins if installed
            // https://github.com/lukaskleinschmidt/kirby-snippet-controller
            if (function_exists('snippet_controller')) {
                $data = snippet_controller($name, $data);
            }

            // merge data with...
            $storybook = \Bnomei\Storybook::singleton();
            if ($storybook->option('cli')) {
                $data = $storybook->loadData(
                    $data,
                    $name
                );
            }

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

if (!function_exists('storybook_block')) {
    function storybook_block(string $type, array $content = []): \Kirby\Cms\Block
    {
        return new \Kirby\Cms\Block([
            'type' => $type,
            'content' => $content,
        ]);
    }
}

if (!function_exists('storybook_slots')) {
    function storybook_slots(array $content = [], ?string $file = null): \Kirby\Template\Slots
    {
        if (!$file) {
            $file = \Kirby\Toolkit\Str::random(5) . '.php';
        }
        $snippet = new \Kirby\Template\Snippet($file);
        $slots = [];
        foreach ($content as $key => $value) {
            $slots[$key] = new \Kirby\Template\Slot($snippet, $key, $value);
        }

        return new \Kirby\Template\Slots($snippet, $slots);
    }
}
