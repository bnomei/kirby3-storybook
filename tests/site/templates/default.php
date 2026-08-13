<?php

use Bnomei\Storybook;

snippet('demo', [/* no $date */ 'date' => 'demo']);

var_dump(Storybook::singleton()->snippets());
var_dump(Storybook::singleton()->templates());
