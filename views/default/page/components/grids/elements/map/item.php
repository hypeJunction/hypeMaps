<?php

$item = elgg_extract('entity', $vars);
$class = elgg_extract('class', $vars);

if (!$item) {
	return true;
}

$type = $item->getType();
$subtype = $item->getSubtype();

$view = "object/$type/$subtype/grids/map/item";

if (elgg_view_exists($view)) {
	echo elgg_view($view, $vars);
	return true;
}

$class = "$class elgg-$type elgg-$type-$subtype hj-map-marker-data";

$id = false;

if (elgg_instanceof($item)) {
	$id = "elgg-entity-$item->guid";
	$uid = $item->guid;
	$ts = max(array($item->time_created, $item->time_updated, $item->last_action));
}

if (!$id) {
	return true;
}

$attr = array(
	'id' => $id,
	'class' => $class,
	'data-uid' => $uid,
	'data-ts' => $ts,
	'data-lat' => $item->getLatitude(),
	'data-long' => $item->getLongitude(),
	'data-location' => $item->getLocation(),
	'data-icon' => $item->getIconURL('small'),
	'data-marker' => hj_maps_get_entity_marker($item),
	'data-title' => $item->title,
);

$attributes = elgg_format_attributes($attr);

$item_view = elgg_view_list_item($item, $vars);

echo "<li $attributes>$item_view</li>";