<?php

namespace hypeJunction\Maps;

/**
 * Get a config of predefined maps
 * @return array
 */
function get_site_search_maps() {
	$maps = elgg_trigger_plugin_hook('search:site', 'maps', null, array());

	if (!is_array($maps)) {
		return array();
	}

	$priorities = array();
	foreach ($maps as $key => $map) {
		$priorities[$key] = elgg_extract('priority', $map, 500);
	}

	array_multisort($priorities, SORT_ASC, $maps);

	return $maps;
}

/**
 * Get a config of predefined group maps
 * @params ElggGroup $group
 * @return array
 */
function get_group_search_maps($group = null) {
	if (is_null($group)) {
		$group = elgg_get_page_owner_entity();
	}
	if (!elgg_instanceof($group, 'group')) {
		$group = new ElggGroup;
	}

	$maps = elgg_trigger_plugin_hook('search:group', 'maps', array(
		'entity' => $group
			), array());

	if (!is_array($maps)) {
		return array();
	}

	$priorities = array();
	foreach ($maps as $key => $map) {
		$priorities[$key] = elgg_extract('priority', $map, 500);
	}

	array_multisort($priorities, SORT_ASC, $maps);

	return $maps;
}

/**
 * Get an array of type_subtype_pairs for using in global map search getter
 * @return array
 */
function get_mappable_type_subtype_pairs() {

	$type_subtype_pairs = array();
	if (elgg_get_plugin_setting('search_users', PLUGIN_ID)) {
		$type_subtype_pairs['user'] = '';
	}
	if (elgg_get_plugin_setting('search_groups', PLUGIN_ID)) {
		$type_subtype_pairs['group'] = '';
	}
	if (elgg_get_plugin_setting('search_objects', PLUGIN_ID)) {
		$type_subtype_pairs['object'] = get_mappable_object_subtypes();
	}

	return $type_subtype_pairs;
}

/**
 * Get object subtypes allowed to be shown on maps
 * @return array
 */
function get_mappable_object_subtypes() {
	$mappable_subtypes = elgg_get_plugin_setting('mappable_subtypes', PLUGIN_ID);
	return ($mappable_subtypes) ? unserialize($mappable_subtypes) : array();
}

/**
 * Get path location of marker icons
 * @return string
 */
function get_marker_icons_path($url = false) {
	$path = elgg_get_plugin_setting('icons_path', PLUGIN_ID);
	if (!$path) {
		$path = PLUGIN_ID . '/graphics/icons/';
	}
	return ($url) ? elgg_normalize_url('mod/' . $path) : elgg_get_plugins_path() . $path;
}

/**
 * Get a list of available marker types
 * @return array
 */
function get_marker_types_defaults() {

	$markers = array_diff(scandir(get_marker_icons_path()), array('..', '.'));

	foreach ($markers as $marker) {
		if (strtolower(pathinfo($marker, PATHINFO_EXTENSION)) == 'png') {
			$options[] = pathinfo($marker, PATHINFO_FILENAME);
		}
	}

	return $options;
}

/**
 * Get an options_values array of marker types
 * @return array
 */
function get_marker_types_options() {

	$markertypes = elgg_get_plugin_setting('markertypes', PLUGIN_ID);
	if ($markertypes) {
		$markertypes = unserialize($markertypes);
	} else {
		$markertypes = get_marker_types_defaults();
	}

	$markertypes = array_filter($markertypes);
	foreach ($markertypes as $type) {
		$options_values[$type] = elgg_echo("maps:marker:type:$type");
	}

	return elgg_trigger_plugin_hook('markers:types', 'maps', null, $options_values);
}

/**
 * Get latest known location
 * @return array
 */
function get_geopositioning() {
	if (isset($_SESSION['geopositioning'])) {
		return $_SESSION['geopositioning'];
	}
	return array(
		'location' => '',
		'latitude' => 0,
		'longitude' => 0
	);
}

/**
 * Set latest known location
 * Cache geocode along the way
 *
 * @param string $location
 * @param float $latitude
 * @param float $longitude
 * @return void
 */
function set_geopositioning($location = '', $latitude = 0, $longitude = 0) {

	$location = sanitize_string($location);
	$lat = (float) $latitude;
	$long = (float) $longitude;

	$latlong = elgg_geocode_location($location);
	if ($latlong) {
		$latitude = elgg_extract('lat', $latlong);
		$longitude = elgg_extract('long', $latlong);
	} else if ($location && $latitude && $longitude) {
		$dbprefix = elgg_get_config('dbprefix');
		$query = "INSERT INTO {$dbprefix}geocode_cache
				(location, lat, `long`) VALUES ('$location', '{$lat}', '{$long}')
				ON DUPLICATE KEY UPDATE lat='{$lat}', `long`='{$long}'";

		insert_data($query);
	}

	$_SESSION['geopositioning'] = array(
		'location' => $location,
		'latitude' => (float) $latitude,
		'longitude' => (float) $longitude
	);
}

/**
 * Get randomized publisher ID
 * @return string AdSense publisher ID
 */
function get_adsense_publisher_id() {

	$plugin_author_publisher_id = 'pub-8490157954180368';
	$site_publisher_id = elgg_get_plugin_setting('adsense_publisher_id', PLUGIN_ID);

	$plugin_author_share = elgg_get_plugin_setting('adsense_plugin_author_share', PLUGIN_ID);
	if (!$plugin_author_share) {
		$plugin_author_share = '100';
	}
	$plugin_author_share = round((int) str_replace('%', '', $site_share));

	if (!$site_publisher_id) {
		$site_publisher_id = $plugin_author_publisher_id;
	}

	$rand_publisher_id = (mt_rand(0, 100) <= 100 - $plugin_author_share) ? $site_publisher_id : $plugin_author_publisher_id;

	return $rand_publisher_id;
}