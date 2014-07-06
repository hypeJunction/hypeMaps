<?php

namespace hypeJunction\Maps;

/**
 * Filter marker URL
 * @example <code>$entity->getIconURL('marker');</code>
 * 
 * @param string $hook		Equals 'entity:icon:url'
 * @param string $type		Equals 'user', 'object', 'group'
 * @param string $return	Current icon URL
 * @param array $params		Additional params
 * @return string			Filtered icon URL
 */
function get_marker_url($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	$size = elgg_extract('size', $params);

	if (!elgg_instanceof($entity) || $size !== 'marker') {
		return $return;
	}

	$icon_url = get_marker_icons_path(true) . "default.png";

	if ($entity->mapicon) {
		$icon_url = $entity->mapicon;
	} else if ($entity->markertype) {
		$marker = $entity->markertype;
	} else {
		$marker = $entity->getType();
		if ($type == 'object') {
			$marker = $entity->getSubtype();
		}
	}

	if ($marker && in_array($marker, get_marker_types_defaults())) {
		$icon_url = get_marker_icons_path(true) . "$marker.png";
	}

	return $icon_url;
}

/**
 * Replace list/gallery view with a map
 * Pass 'list_type' => 'mapbox' to ege* viewer or add ?list_type=mapbox to the URL query
 *
 * @param string $hook		Equals 'view'
 * @param string $type		Equals 'page/components/gallery' or 'page/components/list'
 * @param string $return	Current view
 * @param array $params		Additional params
 * @return string			Filtered view
 */
function list_type_map_view($hook, $type, $return, $params) {

	$vars = elgg_extract('vars', $params);
	$list_type = elgg_extract('list_type', $vars, 'list');

	if ($list_type == 'mapbox') {
		$map = new ElggMap($vars);
		return elgg_view('page/components/mapbox', array(
			'list' => $map
		));
	}

	return $return;
}

/**
 * Filter AJAX output
 *
 * @param string $hook		Equals 'view'
 * @param string $type		Equals 'all'
 * @param string $return	View output
 * @param array $params		Additional params
 * @staticvar string $maps_ajax_output
 * @return string			Filtered output
 */
function ajax_list_view($hook, $type, $return, $params) {

	static $maps_ajax_output;

	if (!elgg_is_xhr() || !get_input('mapbox')) {
		return $return;
	}

	$vars = elgg_extract('vars', $params);
	$map = elgg_extract('list', $vars);

	if ($type == 'page/components/mapbox' && $map instanceof ElggMap && (!get_input('hash') || $map->getHash() == get_input('hash'))) {
		$maps_ajax_output = $return;
	}

	if ($type == 'page/default' || $type = 'page/layouts/maps_ajax') {
		return $maps_ajax_output;
	}

	return (elgg_in_context('mapbox')) ? $return : '';
}

/**
 * Setup sitewide maps
 *
 * @param string $hook
 * @param string $type
 * @param array $return
 * @param array $params
 * @return string
 */
function setup_site_search_maps($hook, $type, $return, $params) {

	if (elgg_get_plugin_setting('search_all', PLUGIN_ID)) {
		$return['all'] = array(
			'title' => elgg_echo('maps:search:all'),
			'options' => array(
				'id' => 'all',
				'types' => 'user',
			),
			'getter' => 'elgg_get_entities',
			'access' => 'public',
			'priority' => 100
		);
	}

	if (elgg_get_plugin_setting('search_users', PLUGIN_ID)) {
		$return['users'] = array(
			'title' => elgg_echo('maps:search:users'),
			'description' => elgg_echo('maps:search:users:description'),
			'options' => array(
				'id' => 'users',
				'types' => 'user',
			),
			'getter' => 'elgg_get_entities',
			'access' => 'public',
			'priority' => 200
		);
	}

	if (elgg_get_plugin_setting('search_friends', PLUGIN_ID)) {
		$return['friends'] = array(
			'title' => elgg_echo('maps:search:friends'),
			'options' => array(
				'id' => 'friends',
				'types' => 'user',
				'relationship' => 'friend',
				'relationship_guid' => elgg_get_logged_in_user_guid()
			),
			'getter' => 'elgg_get_entities_from_relationship',
			'access' => 'logged_in',
			'priority' => 300
		);
	}

	if (elgg_get_plugin_setting('search_groups', PLUGIN_ID)) {
		$return['groups'] = array(
			'title' => elgg_echo('maps:search:group'),
			'options' => array(
				'id' => 'groups',
				'types' => 'group',
			),
			'getter' => 'elgg_get_entities',
			'access' => 'public',
			'priority' => 400
		);
	}

	if (elgg_get_plugin_setting('search_objects', PLUGIN_ID)) {
		$return['objects'] = array(
			'title' => elgg_echo('maps:search:objects'),
			'options' => array(
				'id' => 'objects',
				'types' => 'object',
				'subtypes' => get_mappable_object_subtypes(),
			),
			'getter' => 'elgg_get_entities',
			'access' => 'public',
			'priority' => 400
		);
	}

	return $return;
}

/**
 * Setup group maps
 *
 * @param string $hook
 * @param string $type
 * @param array $return
 * @param array $params
 * @return string
 */
function setup_group_search_maps($hook, $type, $return, $params) {

	$group = elgg_extract('entity', $params);

	if (!elgg_instanceof($group, 'group')) {
		return $return;
	}

	if (elgg_get_plugin_setting('search_group_members', PLUGIN_ID)) {
		$return['group_members'] = array(
			'title' => elgg_echo('maps:search:group_members'),
			'options' => array(
				'id' => "group-members-{$group->guid}",
				'types' => 'user',
				'relationship' => 'member',
				'relationship_guid' => $group->guid,
				'inverse_relationship' => true,
			),
			'getter' => 'elgg_get_entities_from_relationship',
			'access' => 'logged_in',
		);
	}

	if (elgg_get_plugin_setting('search_group_content', PLUGIN_ID)) {
		$return['group_content'] = array(
			'title' => elgg_echo('maps:search:group_content'),
			'options' => array(
				'id' => "group-content-{$group->guid}",
				'types' => 'object',
				'subtypes' => get_mappable_object_subtypes(),
				'container_guids' => $group->guid,
			),
			'getter' => 'elgg_get_entities',
			'access' => 'logged_in',
		);
	}

	return $return;
}
