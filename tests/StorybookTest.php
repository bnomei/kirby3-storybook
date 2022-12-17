<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\Storybook;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

final class StorybookTest extends TestCase
{
    public function testSingleton()
    {
        // create
        $storybook = Storybook::singleton();
        $this->assertInstanceOf(Storybook::class, $storybook);

        // from static cached
        $storybook = Storybook::singleton();
        $this->assertInstanceOf(Storybook::class, $storybook);
    }

    public function testOption()
    {
        $storybook = new Storybook([
            'debug' => true,
        ]);

        $this->assertTrue($storybook->option('debug'));

        $this->assertIsArray($storybook->option());
    }

    public function test__construct()
    {
        $storybook = new Storybook();
        $this->assertInstanceOf(Storybook::class, $storybook);
    }
}
