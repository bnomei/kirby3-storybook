<?php
// if autoloading from yml is not good enough use the helpers directly like this
/*
extract(storybook([
    'slots' => storybook_slots([
        'header' => 'Head from Slot',
        'body' => 'Body from Slot'
    ])
]), EXTR_SKIP); */
?>

<article>
    <?php if ($header = $slots->header()) { ?>
        <header>
            <?= $header ?>
        </header>
    <?php } ?>

    <?= $slots->body() ?>
</article>
