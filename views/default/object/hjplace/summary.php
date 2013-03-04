<?php

$entity = elgg_extract('entity', $vars);

$title = elgg_view('object/hjplace/elements/title', $vars);
$metadata = elgg_view('object/hjplace/elements/menu', $vars);
$subtitle = elgg_view('object/hjplace/elements/location', $vars);
$tags = elgg_view('object/hjplace/elements/tags', $vars);

if (!elgg_in_context('map-view') && HYPEMAPS_SUMMARY_MAP) {
	elgg_load_js('maps.base.js');

	$vars['height'] = '100px';
	$content = elgg_view('framework/maps/maps/location', $vars);
}

$content .= elgg_view('object/hjplace/elements/briefdescription', $vars);

$vars['size'] = 'small';
$icon = elgg_view('object/hjplace/elements/icon', $vars);
$body = elgg_view('object/elements/summary', array(
	'entity' => $entity,
	'title' => $title,
	'metadata' => $metadata,
	'subtitle' => $subtitle,
	'tags' => $tags,
	'content' => $content
));

echo elgg_view_image_block($icon, $body);