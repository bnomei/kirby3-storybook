<?php
// if autoloading from yml is not good enough use the helpers directly like this
/*
extract(storybook([
    'block' => new \Kirby\Cms\Block([
        'type' => 'text',
        'content' => [
            'text' => '...'
        ],
    ]),
]), EXTR_SKIP); */
?>

<?= $block->text();
