<?php

$value = elgg_extract('value', $vars, null);
$entity = elgg_extract('entity', $vars, null);

if (HYPEMAPS_LINK_MAP) {
	$url = "maps/map/$entity->guid?location=$value";
	$class = "hj-maps-popup";
	$trusted = true;
} else {
	$url = "http://maps.google.com/maps?q=$value";
	$target = "_blank";
}
echo elgg_view('output/url', array(
	'text' => $value,
	'href' => $url,
	'class' => $class,
	'is_trusted' => $trusted,
	'target' => $target
));