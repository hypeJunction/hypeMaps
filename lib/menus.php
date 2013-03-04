<?php

if (HYPEMAPS_INTERFACE_VICINITY || HYPEMAPS_INTERFACE_PLACES) {
	elgg_register_menu_item('site', array(
		'name' => 'maps',
		'text' => elgg_echo('hj:maps:places'),
		'href' => 'places',
		'priority' => 400
	));
}

//elgg_register_plugin_hook_handler('register', 'menu:hjentityhead', 'hj_maps_places_entity_head_menu');
elgg_register_plugin_hook_handler('register', 'menu:list_filter', 'hj_maps_list_filter_menu');
elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'hj_maps_owner_block_menu');


function hj_maps_list_filter_menu($hook, $type, $return, $params) {

	$handler = elgg_extract('handler', $params);
	$items_handler = elgg_extract('items_handler', $params);
	$list_id = elgg_extract('list_id', $params);

	if ($handler != 'maps') {
		return $return;
	}

	$url = full_url();

	$list_types = array('map', 'table');

	$i = 0;

	$list_type = get_input("__list_type_$list_id", 'map');
	foreach ($list_types as $lt) {
		$i++;
		$items[] = array(
			'name' => "toggle:list_type:$lt",
			'text' => elgg_echo("hj:maps:list_type_toggle:$lt"),
			'href' => elgg_http_add_url_query_elements($url, array("__list_type_$list_id" => $lt)),
			'section' => 'list_type_toggle',
			'selected' => ($lt == $list_type),
			'priority' => 400 + $i * 10
		);
	}

	foreach ($items as $item) {
		$return[] = ElggMenuItem::factory($item);
	}

	return $return;
}

function hj_maps_register_dashboard_title_buttons($dashboard = 'site') {

	switch ($dashboard) {

		case 'site' :
		case 'owner' :
			if (elgg_is_logged_in()) {
				$user = elgg_get_logged_in_user_entity();

				if (HYPEMAPS_INTERFACE_PLACES) {
					elgg_register_menu_item('title', array(
						'name' => 'create:place',
						'text' => elgg_echo('hj:maps:addnew'),
						'href' => "maps/create/place/$user->guid",
						'class' => 'elgg-button elgg-button-action elgg-button-create-entity',
						'data-toggle' => 'dialog',
						'data-callback' => 'refresh:lists::framework',
						'priority' => 100
					));
				}
			}

			break;

		case 'group' :

			$group = elgg_get_page_owner_entity();

			if ($group->canWriteToContainer()) {

				if (HYPEMAPS_INTERFACE_PLACES) {
					elgg_register_menu_item('title', array(
						'name' => 'create:place',
						'text' => elgg_echo('hj:maps:addnew'),
						'href' => "maps/create/place/$group->guid",
						'class' => 'elgg-button elgg-button-action elgg-button-create-entity',
						'data-toggle' => 'dialog',
						'data-callback' => 'refresh:lists::framework',
						'priority' => 100
					));
				}
			}
			break;
	}
}


function hj_maps_owner_block_menu($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);

	if (HYPEMAPS_GROUP_PLACES && elgg_instanceof($entity, 'group') && $entity->olaces_enable !== 'no') {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'group:places',
					'text' => elgg_echo('hj:maps:group'),
					'href' => "maps/group/$entity->guid"
				));
	} else if (elgg_instanceof($entity, 'user')) {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'user:places',
					'text' => elgg_echo('hj:maps:user'),
					'href' => "maps/dashboard/owner/$entity->username"
				));
	}

	return $return;
}