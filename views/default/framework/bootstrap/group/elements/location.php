<?php

$entity = elgg_extract('entity', $vars);

if ($location = $entity->getLocation()) {
	$location = elgg_view('output/location', array(
		'value' => $location,
		'entity' => $entity
	));
	echo '<div class="elgg-entity-location">' . $location . '</div>';
}
