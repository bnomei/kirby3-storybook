<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\CLI\CLI;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

final class Storybook
{
    private array $options;

    public function __construct(array $options = [])
    {
        $defaults = [
            'debug' => option('debug'),
            'cli' => option('bnomei.storybook.cli'),
        ];
        $this->options = array_merge($defaults, $options);

        foreach ($this->options as $key => $call) {
            if (is_callable($call) && $key == 'cli') {
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
//        if (!\Bnomei\Storybook::singleton()->option('cli')) {
//            return [];
//        }

        // extract will happen in snippet itself
        return $csf;
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
