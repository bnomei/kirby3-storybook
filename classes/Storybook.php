<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;
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
            'storybook_folder' => option('bnomei.storybook.folder'),
            'stories_json' => option('bnomei.storybook.stories.json'),
            'stories_yml' => option('bnomei.storybook.stories.yml'),
            'watcher_errors' => option('bnomei.storybook.watcher.errors'),
        ];
        $this->options = array_merge($defaults, $options);

        foreach ($this->options as $key => $call) {
            if (is_callable($call) && in_array($key, ['cli', 'stories_json', 'watcher_errors', 'storybook_folder'])) {
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

        $csf = $this->loadData($csf, $filepath);

        // extraction of variables will happen in snippet itself
        return $csf;
    }

    public function loadData(array $data, string $filepath): array
    {
        // load data here and NOT via a custom snippet controller so
        // this plugin can be used together with other custom snippet controllers
        // like https://github.com/lukaskleinschmidt/kirby-snippet-controller
        $filePrefix = str_replace('.' . F::extension($filepath), '', $filepath);
        if ($this->option('stories_yml') && empty($csf) && F::exists($filePrefix . '.stories.yml')) {
            $data = Yaml::read($filePrefix . '.stories.yml');
        }
        if ($this->option('stories_json') && empty($csf) && F::exists($filePrefix . '.stories.json')) {
            $data = \Kirby\Kql\Kql::run(Json::read($filePrefix . '.stories.json'));
        }

        return $data;
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

        // since watcher does not get a clean kirby instance but keeps the same while running
        // add as many dynamic as possible. might break if naming is inconsistent between register and filepath.
        $extensionFolders = array_unique(array_map(
            fn ($filepath) => explode('/snippets/', $filepath)[0] . '/snippets/',
            array_filter(
                kirby()->extensions($extension),
                fn ($filepath) => Str::contains($filepath, '/snippets/')
            )
        ));
        $dynFromExtensionFolders = [];
        foreach ($extensionFolders as $dir) {
            if (!Dir::exists($dir)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->in($dir)->name('*.php');
            foreach ($finder as $file) {
                $dynFromExtensionFolders[str_replace('.php', '', $file->getRelativePathname())] = $file->getRealPath();
            }
        }
        // order matters
        $files = array_merge(
            $inRoot,
            kirby()->extensions($extension),
            $dynFromExtensionFolders
        );

        // remove all files that do not exist then return
        return array_filter($files, fn ($filepath) => F::exists($filepath));
    }

    public function generateStorybookFiles(string $root, string $key, string $filepath): bool
    {
        $outputFolder = $this->option('storybook_folder');
        if (!Dir::exists($outputFolder)) {
            throw new \Exception('Storybook folder was not found.');
        }
        if (!F::exists($filepath)) {
            throw new \Exception("File `$filepath` was not found.");
        }

        $fileParts = explode('/', $key);
        $rootUC = ucfirst($root);
        $rootUCSingular = substr($rootUC, 0, strlen($rootUC) - 1);
        $keyUC = implode('/', array_map('ucfirst', $fileParts));
        $title = $rootUC . '/' . $keyUC;
        $camel = ucfirst(Str::camel(str_replace('/', ' ', $key)));
        $local = ucfirst(array_pop($fileParts));
        $base = implode('/', $fileParts);
        $templatePageConfig = str_replace('.php', '.stories.yml', $filepath);

        // html
        $html = "$outputFolder/$root/$base/$local.html";
        if ($root === 'snippets') {
            $out = snippet($key, return: true);
        } elseif ($root === 'templates' && F::exists($templatePageConfig)) {
            $data = $this->loadData([], $filepath);
            if ($id = A::get($data, 'id')) {
                if ($page = page($id)) {
                    $out = $page->render(A::get($data, 'data', []));
                }
            }
        }
        F::write($html, $out);

        // vue, but do not overwrite existing
        $vue = "$outputFolder/$root/$base/$local.vue";
        if (!F::exists($vue)) {
            F::write($vue, '<template src="./' . $local . '.html"></template>');
        }

        // stories.js, but do not overwrite existing
        $js = "$outputFolder/$root/$base/$local.stories.js";
        if (!F::exists($js)) {
            F::write(
                $js,
                "import My$camel from './$local.vue';

export default {
  title: '$title',
  component: My$camel,
};

export const $rootUCSingular = {
  args: {},
};
"
            );
        }

        return true;
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
