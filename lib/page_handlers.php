<?php

elgg_register_page_handler('places', 'hj_maps_page_handler');
elgg_register_page_handler('maps', 'hj_maps_page_handler');

function hj_maps_page_handler($page) {

	elgg_load_css('maps.base.css');
	elgg_load_js('maps.google.js');
	elgg_load_js('maps.base.js');

	$plugin = 'hypeMaps';
	$shortcuts = hj_framework_path_shortcuts($plugin);
	$pages = $shortcuts['pages'] . 'maps/';

	elgg_push_breadcrumb(elgg_echo('hj:maps:places'), 'maps');

	switch ($page[0]) {

		case 'map' :
			$guid = elgg_extract(1, $page, 0);
			if ($entity = get_entity($guid)) {
				set_input('guid', $guid);
				set_input('location', $entity->getLocation());
			}
			if (!get_input('location', false)) {
				return false;
			}
			include "{$pages}maps/location.php";
			return true;
			break;

		case 'marker' :
			$guid = elgg_extract(1, $page, 0);
			$size = elgg_extract(2, $page, 'tiny');
			set_input('e', $guid);
			set_input('size', $size);
			include "{$pages}marker.php";
			return true;
			break;
	}

	if (!HYPEMAPS_INTERFACE_VICINITY && !HYPEMAPS_INTERFACE_PLACES) {
		return false;
	}
	
	switch ($page[0]) {
		default :
		case 'dashboard' :

			$dashboard = elgg_extract(1, $page, 'site');
			set_input('dashboard', $dashboard);

			switch ($dashboard) {

				default :
				case 'site' :
					include "{$pages}dashboard/site.php";
					break;

				case 'owner' :
				case 'friends' :
				case 'groups' :
					gatekeeper();
					if (isset($page[2])) {
						$owner = get_user_by_username($page[2]);
					}
					if (!$owner) {
						return false;
					}

					elgg_set_page_owner_guid($owner->guid);
					$include = "{$pages}dashboard/{$dashboard}.php";

					if (!file_exists($include)) {
						return false;
					}
					include $include;
					break;
			}

			break;

		case 'group' :

			if (!HYPEMAPS_INTERFACE_VICINITY && !HYPEMAPS_INTERFACE_PLACES)
				return false;

			$group_guid = elgg_extract(1, $page, false);
			if (!$group_guid) {
				return false;
			}
			$group = get_entity($group_guid);

			if (!elgg_instanceof($group, 'group')) {
				return false;
			}

			elgg_set_page_owner_guid($group->guid);

			include "{$pages}dashboard/group.php";
			break;

		case 'create' :
			if (!HYPEMAPS_INTERFACE_VICINITY && !HYPEMAPS_INTERFACE_PLACES)
				return false;

			gatekeeper();

			list($action, $subtype, $container_guid) = $page;

			if (!$subtype) {
				return false;
			}

			if (!$container_guid) {
				$container_guid = elgg_get_logged_in_user_guid();
			}

			elgg_set_page_owner_guid($container_guid);

			set_input('container_guid', $container_guid);

			$include = "{$pages}create/{$subtype}.php";

			if (!file_exists($include)) {
				return false;
			}

			include $include;
			break;

		case 'edit' :
			gatekeeper();

			list($action, $guid) = $page;

			set_input('guid', $guid);

			$include = "{$pages}{$action}/object.php";

			if (!file_exists($include)) {
				return false;
			}

			include $include;
			break;

		case 'view' :
			if (!isset($page[1])) {
				return false;
			}
			$entity = get_entity($page[1]);

			if (!$entity)
				return false;

			$sidebar = elgg_view('framework/maps/dashboard/sidebar', array('entity' => $entity));

			echo elgg_view_page($entity->title, elgg_view_layout('framework/entity', array('entity' => $entity, 'sidebar' => $sidebar)));
			break;
	}

	return true;
}
