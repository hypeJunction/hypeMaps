<?php

$entity = $vars['entity'];

if (!elgg_instanceof($entity, 'object', 'hjplace'))
	return true;

if (HYPEMAPS_PLACES_COVER) {
	echo elgg_view('object/hjplace/elements/cover', $vars);
}

echo elgg_view('object/hjplace/elements/description', $vars);
echo elgg_view('object/hjplace/elements/tags', $vars);
echo elgg_view('object/hjplace/elements/type', $vars);
echo elgg_view('object/hjplace/elements/location', $vars);

echo elgg_view('framework/maps/maps/location', $vars);

echo elgg_view('object/hjplace/elements/comments', $vars);