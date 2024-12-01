# Kirby Storybook

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-storybook?color=ae81ff&icon=github&label)
[![Discord](https://flat.badgen.net/badge/discord/bnomei?color=7289da&icon=discord&label)](https://discordapp.com/users/bnomei)
[![Buymecoffee](https://flat.badgen.net/badge/icon/donate?icon=buymeacoffee&color=FF813F&label)](https://www.buymeacoffee.com/bnomei)

Kirby Plugin to generate [Storybook](https://storybook.js.org/) stories from PHP snippets and templates.

<img src="https://raw.githubusercontent.com/bnomei/kirby3-storybook/main/screenshot.png" alt="screenshot" style="max-width: 50%;" />

## Install

### Plugin

Using composer:

```bash
composer global require getkirby/cli
composer require bnomei/kirby3-storybook --dev
```

You need to install the CLI with composer since this plugin depends on the CLI to be available either globally or locally.

### Storybook

Please refer to the [official docs](https://storybook.js.org/docs/get-started/install) on how to install Storybook if in doubt.

```bash
npx storybook@latest init --type vue3
# select vite as bundler and then...
npm install @vitejs/plugin-vue --save-dev
```

> [!TIP]
> I used Storybook@^8.4 and Vue3 with Vite as bundler for my test.

### Vite Config

If you are using Vite as your bundler you might need to adjust the `vite.config.mjs` to properly load Vue components.

```js
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  plugins: [vue()],
});
```

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

> [!NOTE]
> Make sure you can run storybook after installation at least once without errors. Then remove the demo files or copy them to a different location in case you need them for reference (like I usually do).

Secondly, in a different shell run the file watcher powered by the Kirby Storybook plugin.

```bash
kirby storybook:watch
```

The file watcher provided by this plugin needs the Kirby CLI and has various options for interval, displaying errors, running only once and a file pattern match. Call with `--help` for details.

Some examples:

```bash
kirby storybook:watch --help
kirby storybook:watch --errors --once
kirby storybook:watch --interval 5000
kirby storybook:watch --pattern article
kirby storybook:watch --pattern '/.*blocks\/.*/'
```

### Generated Files

The plugin will use the file watcher to monitor your Snippet/Template files and their story config files (aka `*.stories.yml|json`). If any of these files changes it will generate or overwrite the corresponding files in your Storybook `stories` folder. Creating subfolders as needed to match Kirby's extension registry (like `snippets/blocks`). It will NOT remove any files. There are three files created for each story.

- `Example.html` contains the rendered HTML and will be **overwritten on changes** to the source files.
- `Example.stories.js` defines details about your story for Storybook, like title or variants. It will only be created if missing. You can edit it as you like.
- `Example.vue` standard Vue SFC. It references to the HTML file. This file allows you to add custom js/css or when the source is finalized remove the reference, copy the HTML into the vue-file and add support for variants etc.

#### Adding your CSS and JS assets

You could add the reference your a single css file manually with `<style src="./../../app.css"></style>` and import all your scripts to each vue SFC. But my suggested method [out of 6](https://betterprogramming.pub/6-ways-to-configure-global-styles-for-storybook-faa1517aaf1a) would be to import your assets in the `./storybook/preview.js` and/or `.storybook/main.js` that storybook created. See example below:

**./storybook/preview.ts**
```diff
+ import './../assets/css/app.css'
+ import "./../assets/js/alpine.min"

import type { Preview } from "@storybook/vue3";

const preview: Preview = {
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
};

export default preview;
```

**./storybook/main.ts**
```diff
  ...
  "docs": {
    "docsPage": true
  },
+  "previewHead": (head) => (`
+    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" />
+    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
+    ${head}
+  `),
```

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
- [Symfony Finder](https://symfony.com/doc/current/components/finder.html)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-storybook/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

