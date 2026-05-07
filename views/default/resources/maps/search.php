<?php

/**
 * Maps search resource view
 *
 * @uses $vars['id']
 */

$id = elgg_extract('id', $vars);

$maps = hypeJunction\Maps\get_site_search_maps();
if (empty($maps)) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$ids = array_keys($maps);
if (!$id) {
	$id = $ids[0];
}

$map = elgg_extract($id, $maps);
if (!$map) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$map['filter_context'] = 'search';

$title = elgg_extract('title', $map, elgg_echo('maps:untitled'));

elgg_push_breadcrumb(elgg_echo('maps'), 'maps');
elgg_push_breadcrumb($title);

$filter = elgg_view('framework/maps/filters/site', $map);

if (elgg_view_exists("framework/maps/search/$id/map")) {
	$content = elgg_view("framework/maps/search/$id/map", $map);
} else {
	$content = elgg_view('framework/maps/search/_default/map', $map);
}

if (elgg_view_exists("framework/maps/search/$id/sidebar")) {
	$sidebar = elgg_view("framework/maps/search/$id/sidebar", $map);
} else {
	$sidebar = elgg_view('framework/maps/search/_default/sidebar', $map);
}

$layout_vars = [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => $sidebar,
];

$layout_vars = elgg_trigger_plugin_hook('layout', 'maps', [
	'segments' => ['search', $id],
	'handler' => 'maps',
], $layout_vars);

if (empty($layout_vars['content'])) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$layout_name = elgg_is_xhr() ? 'maps_ajax' : 'default';
$pageshell = elgg_is_xhr() ? 'maps_ajax' : 'default';

$layout = elgg_view_layout($layout_name, $layout_vars);
echo elgg_view_page($title, $layout, $pageshell);
