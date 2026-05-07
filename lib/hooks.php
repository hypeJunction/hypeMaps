<?php

namespace hypeJunction\Maps;

use Elgg\Event;

/**
 * @param Event $hook Event being handled
 * @return mixed
 */
function get_marker_url(Event $hook) {
	$entity = $hook->getEntityParam();
	$params = $hook->getParams();
	$size = elgg_extract('size', $params);

	if (!$entity instanceof \ElggEntity || $size !== 'marker') {
		return;
	}

	$icon_url = get_marker_icons_path(true) . 'default.png';
	$type = $hook->getType();
	$marker = null;

	if ($entity->mapicon) {
		$icon_url = $entity->mapicon;
	} elseif ($entity->markertype) {
		$marker = $entity->markertype;
	} else {
		$marker = $entity->getType();
		if ($type === 'object') {
			$marker = $entity->getSubtype();
		}
	}

	if ($marker && in_array($marker, get_marker_types_defaults())) {
		$icon_url = get_marker_icons_path(true) . "{$marker}.png";
	}

	return $icon_url;
}

/**
 * @param Event $hook Event being handled
 * @return mixed
 */
function list_type_map_view(Event $hook) {
	$vars = $hook->getParam('vars', []);
	$list_type = elgg_extract('list_type', $vars, 'list');

	if ($list_type !== 'mapbox') {
		return;
	}

	$map = new ElggMap($vars);
	return elgg_view('page/components/mapbox', ['list' => $map]);
}

/**
 * @param Event $hook Event being handled
 * @return mixed
 */
function ajax_list_view(Event $hook) {
	static $maps_ajax_output;

	if (!elgg_is_xhr() || !get_input('mapbox')) {
		return;
	}

	$type = $hook->getType();
	$vars = $hook->getParam('vars', []);
	$map = elgg_extract('list', $vars);

	if ($type === 'page/components/mapbox' && $map instanceof ElggMap && (!get_input('hash') || $map->getHash() === get_input('hash'))) {
		$maps_ajax_output = $hook->getValue();
	}

	if ($type === 'page/default' || $type === 'page/layouts/maps_ajax') {
		return $maps_ajax_output;
	}

	return elgg_in_context('mapbox') ? $hook->getValue() : '';
}

/**
 * @param Event $hook Event being handled
 * @return mixed
 */
function setup_site_search_maps(Event $hook) {
	$return = $hook->getValue();
	if (!is_array($return)) {
		$return = [];
	}

	if (elgg_get_plugin_setting('search_all', PLUGIN_ID)) {
		$return['all'] = ['title' => elgg_echo('maps:search:all'), 'options' => ['id' => 'all', 'types' => 'user'], 'getter' => 'elgg_get_entities', 'access' => 'public', 'priority' => 100];
	}

	if (elgg_get_plugin_setting('search_users', PLUGIN_ID)) {
		$return['users'] = ['title' => elgg_echo('maps:search:users'), 'description' => elgg_echo('maps:search:users:description'), 'options' => ['id' => 'users', 'types' => 'user'], 'getter' => 'elgg_get_entities', 'access' => 'public', 'priority' => 200];
	}

	if (elgg_get_plugin_setting('search_friends', PLUGIN_ID)) {
		$return['friends'] = ['title' => elgg_echo('maps:search:friends'), 'options' => ['id' => 'friends', 'types' => 'user', 'relationship' => 'friend', 'relationship_guid' => elgg_get_logged_in_user_guid()], 'getter' => 'elgg_get_entities', 'access' => 'logged_in', 'priority' => 300];
	}

	if (elgg_get_plugin_setting('search_groups', PLUGIN_ID)) {
		$return['groups'] = ['title' => elgg_echo('maps:search:group'), 'options' => ['id' => 'groups', 'types' => 'group'], 'getter' => 'elgg_get_entities', 'access' => 'public', 'priority' => 400];
	}

	if (elgg_get_plugin_setting('search_objects', PLUGIN_ID)) {
		$return['objects'] = ['title' => elgg_echo('maps:search:objects'), 'options' => ['id' => 'objects', 'types' => 'object', 'subtypes' => get_mappable_object_subtypes()], 'getter' => 'elgg_get_entities', 'access' => 'public', 'priority' => 400];
	}

	return $return;
}

/**
 * @param Event $hook Event being handled
 * @return mixed
 */
function setup_group_search_maps(Event $hook) {
	$return = $hook->getValue();
	if (!is_array($return)) {
		$return = [];
	}

	$group = $hook->getEntityParam();
	if (!$group instanceof \ElggGroup) {
		return;
	}

	if (elgg_get_plugin_setting('search_group_members', PLUGIN_ID)) {
		$return['group_members'] = ['title' => elgg_echo('maps:search:group_members'), 'options' => ['id' => "group-members-{$group->guid}", 'types' => 'user', 'relationship' => 'member', 'relationship_guid' => $group->guid, 'inverse_relationship' => true], 'getter' => 'elgg_get_entities', 'access' => 'logged_in'];
	}

	if (elgg_get_plugin_setting('search_group_content', PLUGIN_ID)) {
		$return['group_content'] = ['title' => elgg_echo('maps:search:group_content'), 'options' => ['id' => "group-content-{$group->guid}", 'types' => 'object', 'subtypes' => get_mappable_object_subtypes(), 'container_guids' => $group->guid], 'getter' => 'elgg_get_entities', 'access' => 'logged_in'];
	}

	return $return;
}
