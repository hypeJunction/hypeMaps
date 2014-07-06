<?php

namespace hypeJunction\Maps;

/**
 * Handle map pages and markers
 *
 * @param array $page
 * @param string $handler
 * @return boolean
 */
function page_handler($page, $handler) {

	elgg_push_breadcrumb(elgg_echo('maps'), 'maps');

	switch ($page[0]) {

		default :
		case 'search' :

			$maps = get_site_search_maps();

			$ids = array_keys($maps);
			$id = elgg_extract(1, $page, $ids[0]);

			$map = elgg_extract($id, $maps, false);

			if (!$map) {
				return false;
			}

			$map['filter_context'] = 'search';

			$title = elgg_extract('title', $map, elgg_echo('maps:untitled'));
			elgg_push_breadcrumb($title);

			$filter = elgg_view("framework/maps/filters/site", $map);
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
			break;

		case 'group' :

			$group_guid = elgg_extract(1, $page);
			$group = get_entity($group_guid);

			if (!elgg_instanceof($group, 'group')) {
				return false;
			}

			$maps = get_group_search_maps($group);

			$ids = array_keys($maps);
			$id = elgg_extract(2, $page, $ids[0]);

			$map = elgg_extract($id, $maps, false);

			if (!$map) {
				return false;
			}

			$title = elgg_extract('title', $map, elgg_echo('maps:untitled'));
			elgg_push_breadcrumb($title);

			$filter = false;
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
			break;
	}

	$layout = (elgg_is_xhr()) ? 'maps_ajax' : 'content';
	$pageshell = (elgg_is_xhr()) ? 'maps_ajax' : 'default';
	$layout_vars = array(
		'title' => $title,
		'content' => $content,
		'filter' => $filter,
		'sidebar' => $sidebar,
	);

	$layout_vars = elgg_trigger_plugin_hook('layout', 'maps', array(
		'segments' => $page,
		'handler' => $handler,
			), $layout_vars);

	if (empty($layout_vars['content'])) {
		return false;
	}

	$layout = elgg_view_layout($layout, $layout_vars);
	echo elgg_view_page($title, $layout, $pageshell);

	return true;
}