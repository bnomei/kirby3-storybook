# Kirby 3 Storybook

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-storybook?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-storybook?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-storybook)](https://travis-ci.com/bnomei/kirby3-storybook)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-storybook)](https://coveralls.io/github/bnomei/kirby3-storybook)
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-storybook)](https://codeclimate.com/github/bnomei/kirby3-storybook)
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Kirby 3 Plugin to generate Storybook stories from snippets and templates.

![screenshot](https://raw.githubusercontent.com/bnomei/kirby3-storybook/main/screenshot.png)

## Commercial Usage

> <br>
> <b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp;

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Hire me](mailto:b@bnomei.com?subject=Kirby) |


## Install

### Plugin

Using composer:

```bash
composer global require getkirby/cli
composer require bnomei/kirby3-storybook --dev
```

You need to install the CLI with composer since this plugin depends on the CLI to be available either globally or locally.

### Storybook

Please refer to the [official docs](https://storybook.js.org/docs/7.0/vue/get-started/install) on how to install Storybook if in doubt.

```bash
npm install storybook webpack --sav-dev
```

> TIP: I used storybook@^7.0.0-beta.12 and webpack@^5.x.x for my tests.

```bash
npx storybook init --type vue3
```

> TIP: I used vue3 for my tests, but you can stick to vue if you want to keep it consistent to other Kirby components.
>
## Usage

### Creating stories

The plugin can load data for your Snippet/Template files. You can use three different ways for Snippets and two for Templates. Check out the [tests in this repository to see some examples](https://github.com/bnomei/kirby3-storybook/tree/master/tests/site).

#### Snippet stories

Let's assume a snippet named `example.php` in either `site/snippets` or registered via a plugin extension. Add any of these files into the same folder as the snippet.

- `example.stories.yml` containing an array with a key-value pair for each PHP variable you need.
- `example.stories.json` containing a KQL Query you want to be extracted into the snippet.
- or add the `extract(storybook($YOUR_DATA_ARRAY), EXTR_SKIP);` call to the head of your snippet.

#### Template stories

Let's assume a template named `blog.php` in either `site/templates` or registered via a plugin extension. Add any of these files into the same folder as the template.

- `blog.stories.yml` containing either an `id` key with the id of a page to load as value or an array called `virtual` with all data needed for a `Page::factory()` call.
- `blog.stories.json` containing a KQL Query you want to be extracted into the template.

### Storybook and the plugins file watcher

You need to run two tasks. First start Storybook.

```bash
npm run storybook
```

> TIP: Make sure you can run storybook after installation at least once without errors. Then remove the demo files or copy them to a different location in case you need them for reference (like I usually do).

In a different shell run the file watcher.

```bash
kirby storybook:watch
```

The file watcher provided by this plugin needs the Kirby CLI and has various options for interval, displaying errors, running only once and a file pattern match. See help for details.

Some examples:

```bash
kirby storybook:watch --help
kirby storybook:watch --errors --once
kirby storybook:watch --interval 5000
kirby storybook:watch --pattern article
kirby storybook:watch --pattern '/.*blocks\/.*/'
```

### Generated Files

The plugin will use the file watcher to monitor your Snippet/Template files and their story config files (aka `*.stories.yml|json`). If any of these files changes it will generate or overwrite the corresponding files in your Storybook `stories` folder. Creating subfolders as needed to match Kirbys extension registry (like `snippets/blocks`). It will NOT remove any files. There are three files created for each story.

- `Example.html` contains the rendered HTML and will be **overwritten on changes** to the source files.
- `Example.stories.js` defines details about your story for Storybook, like title or variants. It will only be created if missing. You can edit it as you like.
- `Example.vue` standard Vue SFC. It references to the HTML file. This file allows you to add custom js/css or when the source is finalized remove the reference, copy the HTML into the vue-file and add support for variants etc.  For each of these files you need to add the reference your css file manually like with `<style src="./../../app.css"></style>` or you can use a symlink to make the paths to your assets match the assets forlder that storybook expects and autoloads. Call something like this in your index.php once `symlink(__DIR__ . '/assets/', __DIR__ . '/stories/assets');`

## Settings

| bnomei.storybook. | Default    | Description                                                  |
|-------------------|------------|--------------------------------------------------------------|
| cli               | `callback` | detect if is cli and only then inject values                 |
| folder            | `callback` | logic to find you Storybook stories folder, adjust if needed |
| stories.json      | `callback` | if KQL exists allow loading from json files                  |
| stories.yml       | `true`     | allow loading from yml files                                 |
| stories.ignore    | `[]`       | array of string, if matches any file will not be rendered    |


## Dependencies

- [Storybook](https://storybook.js.org/)
- [Kirby CLI](https://github.com/getkirby/cli)
- [CLImate](https://github.com/thephpleague/climate)
- [Symfony Finder](https://symfony.com/doc/current/components/finder.html)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-storybook/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

