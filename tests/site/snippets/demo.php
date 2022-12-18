<?php // extract(storybook(), EXTR_SKIP);?>

<div>Kirby Snippets in Storybook at <?= $date ?></div>

<?php snippet('article', slots: true) ?>
<?php slot('header') ?>
<h1>This is the title</h1>
<?php endslot() ?>

<?php slot('body') ?>
<p>This is some body text</p>
<?php endslot() ?>
<?php endsnippet() ?>
