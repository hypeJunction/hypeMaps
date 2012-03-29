<?php

elgg_load_js('hj.framework.ajax');

elgg_load_js('hj.maps.base');
elgg_load_js('hj.maps.google');
elgg_load_js('hj.maps.googlegears');

//if (elgg_is_xhr()) {
//	echo '<script src="http://maps.googleapis.com/maps/api/js?libraries=geometry,adsense&sensor=true&language={$current_language}&output=svembed" type="text/javascript">';
//	echo '<script src="http://code.google.com/apis/gears/gears_init.js"';
//	echo elgg_view('js/hj/maps/base');
//}
$value = elgg_extract('value', $vars, null);
$entity = elgg_extract('entity', $vars, false);

if (!$entity && is_numeric($value)) {
	$entity = get_entity($value);
}

if (elgg_instanceof($entity)) {
	$action = "action/maps/getter?e=$entity->guid";
	$output = elgg_view('output/url', array(
		'title' => elgg_echo('hj:maps:showmap'),
		'text' => $entity->getLocation(),
		'href' => $action,
		'is_action' => true,
		'rel' => 'fancybox',
		'class' => "hj-ajaxed-map-single-popup",
		'id' => "hj-entity-map-popup-$entity->guid",
			));
} else {
	$action = "action/maps/abstract?location=$value";
	$rand = rand(0, 500);
	$output = elgg_view('output/url', array(
		'title' => elgg_echo('hj:maps:showmap'),
		'text' => $value,
		'href' => $action,
		//'is_action' => true,
		'rel' => 'fancybox',
		'class' => "hj-ajaxed-map-abstract-popup",
		'id' => "hj-entity-map-popup-$rand",
			));
}

echo $output;