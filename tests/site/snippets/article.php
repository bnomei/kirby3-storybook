<article>
    <?php if ($header = $slots->header()): ?>
        <header>
            <?= $header ?>
        </header>
    <?php endif ?>

    <?= $slots->body() ?>
</article>
