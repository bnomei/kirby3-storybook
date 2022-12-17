<?php

Kirby::plugin('storybook/example', [
    'snippets' => [
        'sni' => __DIR__ . '/snippets/sni.php',
    ],
    'templates' => [
        'sni' => __DIR__ . '/templates/sni.php',
    ],
]);
