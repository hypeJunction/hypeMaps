<?php

/**
 * Maps group resource view
 *
 * @uses $vars['group_guid']
 * @uses $vars['id']
 */

$group_guid = (int) elgg_extract('group_guid', $vars);
$group = get_entity($group_guid);

if (!$group instanceof \ElggGroup) {
	throw new \Elgg\EntityNotFoundException();
}

$maps = hypeJunction\Maps\get_group_search_maps($group);
if (empty($maps)) {
	throw new \Elgg\EntityNotFoundException();
}

$ids = array_keys($maps);
$id = elgg_extract('id', $vars);
if (!$id) {
	$id = $ids[0];
}

$map = elgg_extract($id, $maps);
if (!$map) {
	throw new \Elgg\EntityNotFoundException();
}

$title = elgg_extract('title', $map, elgg_echo('maps:untitled'));

elgg_push_breadcrumb(elgg_echo('maps'), 'maps');
elgg_push_breadcrumb($title);

if (elgg_view_exists("framework/maps/search/$id/map")) {
	$content = elgg_view("framework/maps/search/$id/map", $map);
} else {
	$content = elgg_view("framework/maps/search/_default/map", $map);
}

if (elgg_view_exists("framework/maps/search/$id/sidebar")) {
	$sidebar = elgg_view("framework/maps/search/$id/sidebar", $map);
} else {
	$sidebar = elgg_view("framework/maps/search/_default/sidebar", $map);
}

$layout_vars = [
	'title' => $title,
	'content' => $content,
	'filter' => false,
	'sidebar' => $sidebar,
];

$layout_vars = elgg_trigger_plugin_hook('layout', 'maps', [
	'segments' => ['group', $group_guid, $id],
	'handler' => 'maps',
], $layout_vars);

if (empty($layout_vars['content'])) {
	throw new \Elgg\EntityNotFoundException();
}

$layout_name = elgg_is_xhr() ? 'maps_ajax' : 'default';
$pageshell = elgg_is_xhr() ? 'maps_ajax' : 'default';

$layout = elgg_view_layout($layout_name, $layout_vars);
echo elgg_view_page($title, $layout, $pageshell);
