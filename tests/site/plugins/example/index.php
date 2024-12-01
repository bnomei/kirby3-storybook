<?php

Kirby::plugin('storybook/example', [
    'blueprints' => [
        'pages/blog' => __DIR__.'/blueprints/pages/blog.yml',
        'pages/post' => __DIR__.'/blueprints/pages/post.yml',
    ],
    'snippets' => [
        'blocks/heading' => __DIR__.'/snippets/blocks/heading.php',
        'blocks/text' => __DIR__.'/snippets/blocks/text.php',
        'blockquote' => __DIR__.'/snippets/blockquote.php',
    ],
    'templates' => [
        'blog' => __DIR__.'/templates/blog.php',
        'post' => __DIR__.'/templates/post.php',
    ],
]);
