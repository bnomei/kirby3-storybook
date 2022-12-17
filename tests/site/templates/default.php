<?php

snippet('demo', [ /* no $date */ 'date' => 'demo']);

var_dump(Bnomei\Storybook::singleton()->snippets());
var_dump(Bnomei\Storybook::singleton()->templates());
