<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\CLI\CLI;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Symfony\Component\Finder\Finder;

final class Storybook
{
    private array $options;

    public function __construct(array $options = [])
    {
        $defaults = [
            'debug' => option('debug'),
            'cli' => option('bnomei.storybook.cli'), // used in testing
            'stories_json' => option('bnomei.storybook.stories.json'),
            'stories_yml' => option('bnomei.storybook.stories.yml'),
            'watcher_errors' => option('bnomei.storybook.watcher.errors'),
        ];
        $this->options = array_merge($defaults, $options);

        foreach ($this->options as $key => $call) {
            if (is_callable($call) && in_array($key, ['cli', 'stories_json', 'watcher_errors'])) {
                $this->options[$key] = $call();
            }
        }
    }

    public function option(?string $key = null): mixed
    {
        if ($key) {
            return A::get($this->options, $key);
        }

        return $this->options;
    }

    public function csf(array $csf, string $filepath): array
    {
        if (!\Bnomei\Storybook::singleton()->option('cli')) {
            return [];
        }

        // load data here and NOT via a custom snippet controller so
        // this plugin can be used together with other custom snippet controllers
        // like https://github.com/lukaskleinschmidt/kirby-snippet-controller
        $filePrefix = str_replace('.' . F::extension($filepath), '', $filepath);
        if($this->option('stories_yml') && empty($csf) && F::exists($filePrefix . '.yml')) {
            $csf = Yaml::read($filePrefix . '.yml');
        }
        if($this->option('stories_json') && empty($csf) && F::exists($filePrefix . '.json')) {
            $csf = \Kirby\Kql\Kql::run(Json::read($filePrefix . '.json'));
        }

        // extraction of variables will happen in snippet itself
        return $csf;
    }

    public function snippets(): array
    {
        return $this->findPHPFiles('snippets');
    }

    public function templates(): array
    {
        return $this->findPHPFiles('templates');
    }

    private function findPHPFiles(string $extension): array
    {
        $inRoot = [];
        $finder = new Finder();
        $finder->files()->in(kirby()->root($extension))->name('*.php');
        foreach ($finder as $file) {
            $inRoot[str_replace('.php', '', $file->getRelativePathname())] = $file->getRealPath();
        }

        return array_merge(
            $inRoot,
            kirby()->extensions($extension)
        );
    }

    public function generateStorybookFiles(string $root, string $key, string $filepath) {

        F::write(
            kirby()->roots()->index() . '/stories/demo.html',
            snippet('demo', return: true)
        );
    }

    private static $singleton;

    public static function singleton(array $options = []): Storybook
    {
        if (self::$singleton) {
            return self::$singleton;
        }

        self::$singleton = new Storybook($options);

        return self::$singleton;
    }
}
