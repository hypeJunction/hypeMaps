<?php

$location = elgg_extract('location', $vars);
$entity = elgg_extract('entity', $vars, false);
if ($entity) {
	$location = $entity->location;
}

$latlong = elgg_geocode_location($location);

if (!$latlong) {
	echo '<p>' . elgg_echo('hj:maps:geocode:error') . '</p>';
	return true;
}

$attr = array(
	'class' => 'hj-maps-location-map',
	'data-lat' => $latlong['lat'],
	'data-long' => $latlong['long'],
	'data-location' => $location,
	'data-title' => ($entity) ? (isset($entity->title)) ? $entity->title : $entity->name : null,
	'data-icon' => ($entity) ? hj_maps_get_entity_marker($entity) : null,
	'style' => 'width:' . elgg_extract('width', $vars, '100%') . ';height:' . elgg_extract('height', $vars, '500px')
);

$attributes = elgg_format_attributes($attr);

echo "<div $attributes></div>";