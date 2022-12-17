<?php

echo $page->blocks()->toBlocks();

snippet('blockquote', [
   'blockquote' => $page->blockquote()->kt(),
   'cite' => $page->cite()->html(),
]);
