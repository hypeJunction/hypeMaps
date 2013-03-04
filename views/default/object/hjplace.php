<?php

elgg_load_css('maps.base.css');
elgg_load_js('maps.google.js');
elgg_load_js('maps.base.js');

$full = elgg_extract('full_view', $vars, false);

if ($full) {
	echo elgg_view('object/hjplace/full', $vars);
} else {
	echo elgg_view('object/hjplace/summary', $vars);
}