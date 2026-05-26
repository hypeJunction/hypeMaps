<?php

namespace hypeJunction\Maps;

/**
 * Get a config of predefined maps
 * @return array
 */
function get_site_search_maps() {
	$maps = \elgg_trigger_event_results('search:site', 'maps', [], []);
	if (!is_array($maps)) {
		return [];
	}

	$priorities = [];
	foreach ($maps as $key => $map) {
		$priorities[$key] = \elgg_extract('priority', $map, 500);
	}

	array_multisort($priorities, SORT_ASC, $maps);
	return $maps;
}

/**
 * Get a config of predefined group maps
 *
 * @param ElggGroup $group Group entity to look up; defaults to page owner
 * @return array
 */
function get_group_search_maps($group = null) {
	if (is_null($group)) {
		$group = \elgg_get_page_owner_entity();
	}

	if (!$group instanceof \ElggGroup) {
		$group = new \ElggGroup();
	}

	$maps = \elgg_trigger_event_results('search:group', 'maps', ['entity' => $group], []);
	if (!is_array($maps)) {
		return [];
	}

	$priorities = [];
	foreach ($maps as $key => $map) {
		$priorities[$key] = \elgg_extract('priority', $map, 500);
	}

	array_multisort($priorities, SORT_ASC, $maps);
	return $maps;
}

/**
 * Get an array of type_subtype_pairs for using in global map search getter
 * @return array
 */
function get_mappable_type_subtype_pairs() {
	$type_subtype_pairs = [];
	if (\elgg_get_plugin_setting('search_users', PLUGIN_ID)) {
		$type_subtype_pairs['user'] = '';
	}

	if (\elgg_get_plugin_setting('search_groups', PLUGIN_ID)) {
		$type_subtype_pairs['group'] = '';
	}

	if (\elgg_get_plugin_setting('search_objects', PLUGIN_ID)) {
		$type_subtype_pairs['object'] = get_mappable_object_subtypes();
	}

	return $type_subtype_pairs;
}

/**
 * Get object subtypes allowed to be shown on maps
 * @return array
 */
function get_mappable_object_subtypes() {
	$mappable_subtypes = \elgg_get_plugin_setting('mappable_subtypes', PLUGIN_ID);
	if (!$mappable_subtypes) {
		return [];
	}

	$decoded = json_decode($mappable_subtypes, true);
	if (!is_array($decoded)) {
		// Backward compat with serialize()d settings written by older versions
		$decoded = @unserialize($mappable_subtypes, ['allowed_classes' => false]);
	}

	return is_array($decoded) ? $decoded : [];
}

/**
 * Get path location of marker icons
 *
 * @param bool $url If true, return a normalized URL instead of a filesystem path
 * @return string
 */
function get_marker_icons_path($url = false) {
	$path = \elgg_get_plugin_setting('icons_path', PLUGIN_ID);
	if (!$path) {
		return $url ? \elgg_normalize_url('mod/' . PLUGIN_ID . '/graphics/icons/') : __DIR__ . '/../graphics/icons/';
	}

	return $url ? \elgg_normalize_url('mod/' . $path) : __DIR__ . '/../' . $path;
}

/**
 * Get a list of available marker types
 * @return array
 */
function get_marker_types_defaults() {
	$markers = array_diff(scandir(get_marker_icons_path()), ['..', '.']);
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
	$markertypes = \elgg_get_plugin_setting('markertypes', PLUGIN_ID);
	if ($markertypes) {
		$decoded = json_decode($markertypes, true);
		if (!is_array($decoded)) {
			// Backward compat with serialize()d settings written by older versions
			$decoded = @unserialize($markertypes, ['allowed_classes' => false]);
		}

		$markertypes = is_array($decoded) ? $decoded : get_marker_types_defaults();
	} else {
		$markertypes = get_marker_types_defaults();
	}

	$markertypes = array_filter($markertypes);
	foreach ($markertypes as $type) {
		$options_values[$type] = \elgg_echo("maps:marker:type:{$type}");
	}

	return \elgg_trigger_event_results('markers:types', 'maps', [], $options_values);
}

/**
 * Get latest known location
 * @return array
 */
function get_geopositioning() {
	if (isset($_SESSION['geopositioning'])) {
		return $_SESSION['geopositioning'];
	}

	return ['location' => '', 'latitude' => 0, 'longitude' => 0];
}

/**
 * Set latest known location
 * Cache geocode along the way
 *
 * @param string $location  Human-readable address
 * @param float  $latitude  Latitude
 * @param float  $longitude Longitude
 * @return void
 */
function set_geopositioning($location = '', $latitude = 0, $longitude = 0) {
	$lat = (float) $latitude;
	$long = (float) $longitude;
	$latlong = \elgg_trigger_event_results('geocode', 'location', ['location' => $location], false);
	if ($latlong) {
		$latitude = \elgg_extract('lat', $latlong);
		$longitude = \elgg_extract('long', $latlong);
	} elseif ($location && $latitude && $longitude) {
		$prefix = \elgg_get_config('dbprefix');
		$query = "INSERT INTO {$prefix}geocode_cache
                (location, lat, `long`) VALUES (:location, :lat, :long)
                ON DUPLICATE KEY UPDATE lat=:lat2, `long`=:long2";
		elgg()->db->getConnection('write')->executeStatement($query, [
			'location' => $location,
			'lat' => $lat,
			'long' => $long,
			'lat2' => $lat,
			'long2' => $long,
		]);
	}

	$_SESSION['geopositioning'] = ['location' => $location, 'latitude' => (float) $latitude, 'longitude' => (float) $longitude];
}

/**
 * Get randomized publisher ID
 * @return string AdSense publisher ID
 */
function get_adsense_publisher_id() {
	$plugin_author_publisher_id = 'pub-8490157954180368';
	$site_publisher_id = \elgg_get_plugin_setting('adsense_publisher_id', PLUGIN_ID);
	$plugin_author_share = \elgg_get_plugin_setting('adsense_plugin_author_share', PLUGIN_ID);
	if (!$plugin_author_share) {
		$plugin_author_share = '100';
	}

	$plugin_author_share = round((int) str_replace('%', '', $site_share));
	if (!$site_publisher_id) {
		$site_publisher_id = $plugin_author_publisher_id;
	}

	$rand_publisher_id = mt_rand(0, 100) <= 100 - $plugin_author_share ? $site_publisher_id : $plugin_author_publisher_id;
	return $rand_publisher_id;
}
