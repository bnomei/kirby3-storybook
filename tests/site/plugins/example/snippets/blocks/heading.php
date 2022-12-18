<?php
extract(storybook([
    'block' => new \Kirby\Cms\Block([
        'type' => 'text',
        'content' => [
            'text' => 'Illo rerum cupiditate'
        ],
    ]),
]), EXTR_SKIP); ?>

<<?= $level = $block->level()->or('h2') ?>><?= $block->text() ?></<?= $level ?>>
