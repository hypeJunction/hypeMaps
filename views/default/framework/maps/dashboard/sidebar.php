<?php

$entity = elgg_extract('entity', $vars);

if (elgg_instanceof($entity, 'object', 'hjplace')) {
	echo elgg_view('object/hjplace/elements/sidebar', $vars);
	return true;
}

$search_title = elgg_echo('hj:maps:filter');
$search_box = elgg_view('framework/maps/filters/map', $vars);

echo elgg_view_module('aside', $search_title, $search_box);

$search_title = elgg_echo('hj:maps:filter:location');
$search_box = elgg_view('framework/maps/filters/location', $vars);

echo elgg_view_module('aside', $search_title, $search_box);
