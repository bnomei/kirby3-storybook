<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\App;
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
            'stories_ignore' => option('bnomei.storybook.stories.ignore'),
            'watcher_errors' => option('debug'),
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
        if (! \Bnomei\Storybook::singleton()->option('cli')) {
            return [];
        }

        $csf = $this->loadData($csf, $filepath);

        // extraction of variables will happen in snippet itself
        return $csf;
    }

    public function loadData(array $data, string $filepath): array
    {
        // if filepath is not a path but just the name try to find the file
        if (! F::exists($filepath)) {
            $filepath = $this->snippetFileFromName($filepath);
            if (! $filepath) {
                return [];
            }
        }

        $filePrefix = str_replace('.'.F::extension($filepath), '', $filepath);
        if ($this->option('stories_yml') && empty($csf) && F::exists($filePrefix.'.stories.yml')) {
            $data = array_merge(Yaml::read($filePrefix.'.stories.yml'), $data);
        }
        if ($this->option('stories_json') && empty($csf) && F::exists($filePrefix.'.stories.json')) {
            $data = array_merge(\Kirby\Kql\Kql::run(Json::read($filePrefix.'.stories.json')), $data);
        }

        // transform block and slots
        if ($block = A::get($data, 'block')) {
            if (is_array($block)) {
                $data['block'] = storybook_block(
                    A::get($block, 'type'),
                    A::get($block, 'content', [])
                );
            }
        }
        // Passing the $slot or $slots variables to snippets is not supported.
        /*
        if ($slots = A::get($data, 'slots')) {
            if (is_array($slots)) {
                $data['slots'] = storybook_slots($slots);
            }
        }
        */
        if (array_key_exists('slot', $data)) {
            unset($data['slot']);
        }
        if (array_key_exists('slots', $data)) {
            unset($data['slots']);
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
        $finder = new Finder;
        $finder->files()->in(kirby()->root($extension))->name('*.php');
        foreach ($finder as $file) {
            $inRoot[str_replace('.php', '', $file->getRelativePathname())] = $file->getRealPath();
        }

        // since watcher does not get a clean kirby instance but keeps the same while running
        // add as many dynamic as possible. might break if naming is inconsistent between register and filepath.
        $extensionFolders = array_unique(array_map(
            fn ($filepath) => explode('/snippets/', $filepath)[0].'/snippets/',
            array_filter(
                kirby()->extensions($extension),
                fn ($filepath) => Str::contains($filepath, '/snippets/')
            )
        ));
        $dynFromExtensionFolders = [];
        foreach ($extensionFolders as $dir) {
            if (! Dir::exists($dir)) {
                continue;
            }

            $finder = new Finder;
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

        // remove all that match an ignore pattern
        $ignores = array_merge([
            'kirby/config/templates/',
            'kirby/config/blocks/',
        ], $this->option('stories_ignore')
        );
        $files = array_filter($files, function ($filepath) use ($ignores) {
            foreach ($ignores as $ignore) {
                if (Str::contains($filepath, $ignore)) {
                    return false;
                }
            }

            return true;
        });

        // remove all files that do not exist then return
        return array_filter($files, fn ($filepath) => F::exists($filepath));
    }

    public function generateStorybookFiles(string $root, string $key, string $filepath): bool
    {
        $outputFolder = $this->option('storybook_folder');
        if (empty($outputFolder) || ! Dir::exists($outputFolder)) {
            throw new \Exception('Storybook folder was not found.');
        }
        if (! F::exists($filepath)) {
            throw new \Exception("File `$filepath` was not found.");
        }

        $fileParts = explode('/', $key);
        $rootUC = ucfirst($root);
        $rootUCSingular = substr($rootUC, 0, strlen($rootUC) - 1);
        $keyUC = implode('/', array_map('ucfirst', $fileParts));
        $title = $rootUC.'/'.$keyUC;
        $camel = ucfirst(Str::camel(str_replace('/', ' ', $key)));
        $local = ucfirst(array_pop($fileParts));
        $base = implode('/', $fileParts);
        $templatePageConfig = str_replace('.php', '.stories.yml', $filepath);

        // html
        $html = "$outputFolder/$root/$base/$local.html";
        $out = null;
        if ($root === 'snippets') {
            try {
                $out = snippet($key, return: true);
            } catch (\Exception $ex) {
                // forward exception
                throw new \Exception($ex->getMessage());
            }
        } elseif ($root === 'templates' && F::exists($templatePageConfig)) {
            $data = $this->loadData([], $filepath);
            if ($id = A::get($data, 'id')) {
                if ($page = page($id)) {
                    $out = $page->render(A::get($data, 'data', []));
                }
            }
            if ($virtual = A::get($data, 'virtual')) {
                $virtual['parent'] = A::get($virtual, 'parent') ? page(A::get($virtual, 'parent')) : null;
                $page = Page::factory($virtual);
                if ($page) {
                    $out = $page->render(A::get($virtual, 'data', []));
                }
            }
        }
        if ($out === null) { // out empty would be allowed for snippets without content
            throw new \Exception('Rendering of HTML failed. Check if you have all variables defined or link to existing IDs.');
        }
        F::write($html, $out);

        // vue, but do not overwrite existing
        $vue = "$outputFolder/$root/$base/$local.vue";
        if (! F::exists($vue)) {
            F::write($vue, '<template src="./'.$local.'.html"></template>');
        }

        // stories.js, but do not overwrite existing
        // allow various JS formats
        $exists = false;
        foreach(['js', 'jsx', 'mjs', 'ts', 'tsx'] as $ext) {
            $js = "$outputFolder/$root/$base/$local.stories.$ext";
            if (F::exists($js)) {
                $exists = true;
                break;
            }
        }
        if (! $exists) {
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

    private function snippetFileFromName(string $name): ?string
    {
        $kirby = App::instance();
        $names = A::wrap($name);
        $root = $kirby->root('snippets');

        foreach ($names as $name) {
            $file = $root.'/'.$name.'.php';

            if (file_exists($file) === false) {
                $file = $kirby->extensions('snippets')[$name] ?? null;
            }

            if ($file) {
                return F::exists($file) ? $file : null;
            }
        }

        return null;
    }

    public function pattern(string $filepath, ?string $pattern): bool
    {
        if (! $pattern || empty($pattern)) {
            return true;
        }

        if (Str::startsWith($pattern, '/') && Str::endsWith($pattern, '/')) {
            return preg_match($pattern, $filepath) !== 1;
        }

        return Str::contains($filepath, $pattern);
    }

    private static array $checksums = [];

    public function modified(string $filepath): bool
    {
        // check source file and story files in yml and json
        $checksum = F::exists($filepath) ? strval(F::modified($filepath)) : '_';
        $filePrefix = str_replace('.'.F::extension($filepath), '', $filepath);
        if (F::exists($filePrefix.'.stories.yml')) {
            $checksum .= F::exists($filePrefix.'.stories.yml') ? F::modified($filePrefix.'.stories.yml') : '_';
        }
        if (F::exists($filePrefix.'.stories.json')) {
            $checksum .= F::exists($filePrefix.'.stories.json') ? F::modified($filePrefix.'.stories.json') : '_';
        }

        $checksum = md5($checksum);
        $old = A::get(Storybook::$checksums, $filepath);
        if ($old && $old === $checksum) {
            return false;
        }

        Storybook::$checksums[$filepath] = $checksum;

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
