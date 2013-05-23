<?php

$item = elgg_extract('entity', $vars);

if (!$item) {
	return true;
}

$type = $item->getType();
$subtype = $item->getSubtype();

$view = "object/$type/$subtype/grids/map/anchor";

if (elgg_view_exists($view)) {
	echo elgg_view($view, $vars);
	return true;
}

$class = "elgg-$type elgg-$type-$subtype hj-map-marker-anchor";

$id = false;

if (elgg_instanceof($item)) {
	$uid = $item->guid;
	$ts = max(array($item->time_created, $item->time_updated, $item->last_action));
	if ($item->name) {
		$title = $item->name;
	} else {
		$title = $item->title;
	}
	if ($location = $item->getLocation()) {
		$title = "$title: $location";
	} else {
		$location = elgg_echo('hj:maps:locationnotset');
		$title = "$title: $location";
	}
}

$attr = array(
	'class' => $class,
	'data-uid' => $uid,
	'data-ts' => $ts,
	'title' => $title
);


$attributes = elgg_format_attributes($attr);

$item_view = elgg_view("framework/bootstrap/$type/elements/marker", $vars);
$item_view .= elgg_view("framework/bootstrap/$type/elements/distance", $vars);

echo "<li $attributes>$item_view</li>";