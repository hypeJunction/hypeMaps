<?php

function hj_maps_get_entity_marker($entity) {

	if (isset($entity->mapicon)) {
		$icon = $entity->mapicon;
	} else if (isset($entity->markertype)) {
		$icon = elgg_get_config('url') . 'mod/hypeMaps/graphics/icons/' . $entity->markertype . '.png';
	} else {
		if (elgg_instanceof($entity, 'object') && $entity->getIconURL('tiny') !== elgg_normalize_url('_graphics/icons/default/tiny.png')) {
			$icon = elgg_get_config('url') . 'places/marker/' . $entity->guid;
		} elseif (elgg_instanceof($entity, 'user') && $entity->getIconURL('tiny') !== elgg_normalize_url('_graphics/icons/user/defaulttiny.gif')) {
			$icon = elgg_get_config('url') . 'places/marker/' . $entity->guid;
		} else {
			$icon = elgg_get_config('url') . 'mod/hypeMaps/graphics/icons/default.png';
		}
	}

	$icon = elgg_trigger_plugin_hook('hj:maps:mapicon', 'all', array('entity' => $entity), $icon);

	return $icon;
}

function hj_maps_define_default_map_center() {

	$site = elgg_get_site_entity();
	$user = elgg_get_logged_in_user_entity();

	if (!$user || (!$user->getLatitude() || !$user->getLongitude())) {
		$entity = $site;
	} else {
		$entity = $user;
	}

	define('HYPEMAPS_LAT', $entity->getLatitude());
	define('HYPEMAPS_LONG', $entity->getLongitude());
}

function hj_maps_get_mappable_type_subtype_pairs() {
	$setting = elgg_get_plugin_setting('map_type_subtype_pairs', 'hypeMaps');
	$type_subtype_pairs = array('object' => array());
	if ($setting) {
		$setting = explode(',', $setting);
		foreach ($setting as $s) {
			list($type, $subtype) = explode(':', $s);
			if (!isset($type_subtype_pairs[$type])) {
				$type_subtype_pairs[$type] = array();
			}
			if ($subtype != 'default') {
				$type_subtype_pairs[$type][] = $subtype;
			}
		}
	}
	return $type_subtype_pairs;
}

function hj_maps_get_marker_types_options() {

	$options = elgg_get_plugin_setting('markertypes', 'hypeMaps');
	$options = array_map('trim', explode(',', $options));

	foreach ($options as $opt) {
		$marker_types[$opt] = elgg_echo("markertype:value:$opt");
	}

	$marker_types = elgg_trigger_plugin_hook('markertype:values', 'framework:maps', null, $marker_types);

	return $marker_types;
	
}