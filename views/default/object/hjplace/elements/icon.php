<?php

$entity = elgg_extract('entity', $vars);
$size = elgg_extract('size', $vars, 'small');

if ($size == 'small') {
	echo elgg_view('output/img', array(
		'src' => hj_maps_get_entity_marker($entity)
	));
} else {
	echo elgg_view('framework/bootstrap/object/elements/icon', $vars);
}