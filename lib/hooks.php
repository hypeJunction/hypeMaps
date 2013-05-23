<?php

// Custom search clause
elgg_register_plugin_hook_handler('custom_sql_clause', 'framework:lists', 'hj_maps_get_custom_filter_clauses');

function hj_maps_get_custom_filter_clauses($hook, $type, $options, $params) {

	$options = hj_maps_get_custom_search_clause($options);
	$options = hj_maps_get_custom_location_clause($options, $params);
	$options = hj_maps_get_custom_type_subtype_clause($options);

	return $options;
}

function hj_maps_get_custom_search_clause($options, $params = null) {

	$query = get_input("__map_q", false);

	if (!$query || empty($query)) {
		return $options;
	}

	$query = sanitise_string(urldecode($query));

	$dbprefix = elgg_get_config('dbprefix');
	$tag_names = elgg_get_registered_tag_metadata_names();

	$options['joins'][] = "JOIN {$dbprefix}metadata mdtags on e.guid = mdtags.entity_guid";
	$options['joins'][] = "JOIN {$dbprefix}metastrings msntags on mdtags.name_id = msntags.id";
	$options['joins'][] = "JOIN {$dbprefix}metastrings msvtags on mdtags.value_id = msvtags.id";

	$access = get_access_sql_suffix('mdtags');
	$sanitised_tags = array();

	foreach ($tag_names as $tag) {
		$sanitised_tags[] = '"' . sanitise_string($tag) . '"';
	}

	$tags_in = implode(',', $sanitised_tags);

	$options['joins'][] = "JOIN {$dbprefix}objects_entity oe_q ON e.guid = oe_q.guid";
	$options['wheres'][] = "((MATCH(oe_q.title, oe_q.description) AGAINST ('$query')) OR (msntags.string IN ($tags_in) AND msvtags.string = '$query' AND $access))";

	return $options;
}

function hj_maps_get_custom_location_clause($options, $params = null) {

	$list_id = elgg_extract('list_id', $params);
	$list_options = elgg_extract('list_options', $params);

	$loc = get_input('__loc', false);
	if (!$loc || empty($loc)) {
		$loc = get_input('__loc_cache', false);
		if (!$loc || empty($loc)) {
			return $options;
		}
	}

	$loc = urldecode($loc);
	$latlong = elgg_geocode_location($loc);

	if (!$latlong) {
		register_error(elgg_echo('hj:maps:filter:geocode:error'));
		return $options;
	}

	$latitude = $latlong['lat'];
	$longitude = $latlong['long'];

	$user = elgg_get_logged_in_user_entity();

	if ($user) {
		$user_locations = elgg_get_metadata(array(
			'owner_guids' => $user->guid,
			'metadata_names' => array('location', 'temp_location'),
			'metadata_values' => $loc,
			'count' => true
				));

		if (!$user_locations) {
			create_metadata($user->guid, 'temp_location', $loc, '', $user->guid, ACCESS_PUBLIC, true);
		}
	}

	$dbprefix = elgg_get_config('dbprefix');

	$options['joins'][] = "JOIN {$dbprefix}metadata mdlat on e.guid = mdlat.entity_guid";
	$options['joins'][] = "JOIN {$dbprefix}metastrings msnlat on mdlat.name_id = msnlat.id";
	$options['joins'][] = "JOIN {$dbprefix}metastrings msvlat on mdlat.value_id = msvlat.id";

	$options['joins'][] = "JOIN {$dbprefix}metadata mdlong on e.guid = mdlong.entity_guid";
	$options['joins'][] = "JOIN {$dbprefix}metastrings msnlong on mdlong.name_id = msnlong.id";
	$options['joins'][] = "JOIN {$dbprefix}metastrings msvlong ON mdlong.value_id = msvlong.id";

	$order_by_key = elgg_extract('order_by_key', $list_options, "__ord_$list_id");
	$order_by = get_input($order_by_key);
	$dir_key = elgg_extract('direction_key', $list_options, "__dir_$list_id");
	$direction = get_input($dir_key, 'ASC');

	$ratio = 1;
	if (HYPEMAPS_METRIC_SYSTEM == 'SI') {
		$ratio = 1.609344; // convert to km
	}

	if ($order_by == 'maps.distance' || !isset($options['order_by'])) {
		$options['selects'] = array("(((acos(sin(($latitude*pi()/180)) * sin((CAST(msvlat.string AS DECIMAL(52,8))*pi()/180))+cos(($latitude*pi()/180)) * cos((CAST(msvlat.string AS DECIMAL(52,8))*pi()/180)) * cos((($longitude - CAST(msvlong.string AS DECIMAL(52,8)))*pi()/180))))*180/pi())*60*1.1515*$ratio) as distance");
		$options['order_by'] = "distance $direction, e.time_created DESC";
	}

	$rad = (int)get_input('__rad');
	if ($rad && !empty($rad)) {
		$rad = ceil($rad / $ratio); // convert to km
		$options['wheres'][] = "(((acos(sin(($latitude*pi()/180)) * sin((CAST(msvlat.string AS DECIMAL(52,8))*pi()/180))+cos(($latitude*pi()/180)) * cos((CAST(msvlat.string AS DECIMAL(52,8))*pi()/180)) * cos((($longitude - CAST(msvlong.string AS DECIMAL(52,8)))*pi()/180))))*180/pi())*60*1.1515) <= $rad";
	}

	global $XHR_GLOBAL;
	$XHR_GLOBAL['lists'][$list_id]['center']['latitude'] = $latitude;
	$XHR_GLOBAL['lists'][$list_id]['longitude'] = $longitude;
	$XHR_GLOBAL['lists'][$list_id]['radius'] = $rad;
	
	return $options;
}

function hj_maps_get_custom_type_subtype_clause($options) {
	$pairs = get_input('__map_pairs');

	if (!$pairs || empty($pairs)) {
		return $options;
	}

	foreach ($pairs as $pair) {
		list($type, $subtype) = explode(':', $pair);
		if (!isset($type_subtype_pairs[$type])) {
			$type_subtype_pairs[$type] = array();
		}
		if ($subtype != 'default') {
			$type_subtype_pairs[$type][] = $subtype;
		}
	}
	$options['type_subtype_pairs'] = $type_subtype_pairs;

	return $options;
}