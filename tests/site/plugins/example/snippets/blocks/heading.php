<?php
// if autoloading from yml is not good enough use the helpers directly like this
/*
extract(storybook([
    'block' => storybook_block('text', [
        'text' => 'Illo rerum cupiditate'
    ]),
]), EXTR_SKIP); */
?>

<<?= $level = $block->level()->or('h2') ?>><?= $block->text() ?></<?= $level ?>>
