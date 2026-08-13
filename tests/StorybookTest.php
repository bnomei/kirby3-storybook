<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Bnomei\Storybook;
use Kirby\Cms\App;
use Kirby\Filesystem\Dir;

function resolveStorybookFolderForTest(string $indexRoot, array $options = []): ?string
{
    $previousApp = App::instance();

    try {
        new App([
            'roots' => [
                'index' => $indexRoot,
            ],
            'options' => $options,
        ]);

        return (new Storybook)->option('storybook_folder');
    } finally {
        restore_error_handler();
        restore_exception_handler();
        App::instance($previousApp);
    }
}

test('singleton', function () {
    // create
    $storybook = Storybook::singleton();
    expect($storybook)->toBeInstanceOf(Storybook::class);

    // from static cached
    $storybook = Storybook::singleton();
    expect($storybook)->toBeInstanceOf(Storybook::class);
});
test('option', function () {
    $storybook = new Storybook([
        'debug' => true,
    ]);

    expect($storybook->option('debug'))->toBeTrue();

    expect($storybook->option())->toBeArray();
});
test('construct', function () {
    $storybook = new Storybook;
    expect($storybook)->toBeInstanceOf(Storybook::class);
});

test('storybook folder uses stories inside the index root first', function () {
    $projectRoot = sys_get_temp_dir().'/kirby3-storybook-'.bin2hex(random_bytes(8));
    $indexRoot = $projectRoot.'/public';

    try {
        Dir::make($indexRoot.'/stories');
        Dir::make($projectRoot.'/stories');

        expect(resolveStorybookFolderForTest($indexRoot))->toBe($indexRoot.'/stories');
    } finally {
        Dir::remove($projectRoot);
    }
});

test('storybook folder falls back to stories beside the index root', function () {
    $projectRoot = sys_get_temp_dir().'/kirby3-storybook-'.bin2hex(random_bytes(8));
    $indexRoot = $projectRoot.'/public';

    try {
        Dir::make($indexRoot);
        Dir::make($projectRoot.'/stories');

        expect(resolveStorybookFolderForTest($indexRoot))->toBe($indexRoot.'/../stories');
    } finally {
        Dir::remove($projectRoot);
    }
});

test('storybook folder is not discovered recursively', function () {
    $projectRoot = sys_get_temp_dir().'/kirby3-storybook-'.bin2hex(random_bytes(8));
    $indexRoot = $projectRoot.'/public';

    try {
        Dir::make($indexRoot.'/media/archive/stories');

        expect(resolveStorybookFolderForTest($indexRoot))->toBeNull();
    } finally {
        Dir::remove($projectRoot);
    }
});

test('storybook folder supports an explicitly configured location', function () {
    $projectRoot = sys_get_temp_dir().'/kirby3-storybook-'.bin2hex(random_bytes(8));
    $indexRoot = $projectRoot.'/public';
    $customFolder = $projectRoot.'/storybook/output';

    try {
        Dir::make($indexRoot);
        Dir::make($customFolder);

        expect(resolveStorybookFolderForTest($indexRoot, [
            'bnomei.storybook.folder' => $customFolder,
        ]))->toBe($customFolder);
    } finally {
        Dir::remove($projectRoot);
    }
});
