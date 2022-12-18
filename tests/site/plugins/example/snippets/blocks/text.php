<?php
extract(storybook([
    'block' => new \Kirby\Cms\Block([
        'type' => 'text',
        'content' => [
            'text' => 'Non unde eaque nesciunt. Molestias possimus eligendi quidem quis et et. Et vel dolorem ullam libero id et dolor eos qui ut aut possimus maiores. Quidem placeat et praesentium nostrum dicta. Quo tempore distinctio porro expedita ut reiciendis rerum aliquam ut tenetur. Velit nam ut blanditiis sint similique tempora. Nisi aut occaecati enim qui voluptas quia.'
        ],
    ]),
]), EXTR_SKIP); ?>

<?= $block->text();
