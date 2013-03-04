<?php

$entity = elgg_extract('entity', $vars);

if (isset($entity->distance) && $entity->distance >= 0) {
	if (HYPEMAPS_METRIC_SYSTEM == 'SI') {
		$distance = (string)round($entity->distance * 1.609344, 2);
		$distance_str = elgg_echo('hj:maps:radius:SI', array($distance));
	} else {
		$distance = (string)round($entity->distance, 2);
		$distance_str = elgg_echo('hj:maps:radius:US', array($distance));
	}
	echo '<div class="hj-map-anchor-distance">' . $distance_str . '</div>';
}
