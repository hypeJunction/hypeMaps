<?php

namespace hypeJunction\Maps;

/**
 * Setup menus
 */
function pagesetup() {

	elgg_register_menu_item('site', array(
		'name' => 'maps',
		'text' => elgg_echo('maps'),
		'href' => 'maps',
	));
}

/**
 * Setup group menus
 */
function pagesetup_groups() {

	$page_owner = elgg_get_page_owner_entity();
	if (elgg_instanceof($page_owner, 'group')) {
		$group_maps = get_group_search_maps($page_owner);
		if (is_array($group_maps)) {
			foreach ($group_maps as $id => $gm) {

				$groupoption = "maps_{$id}_enable";
				if ($page_owner->$groupoption != 'no') {
					elgg_register_menu_item('owner_block', array(
						'name' => "maps:$id",
						'text' => elgg_extract('title', $gm),
						'href' => "maps/group/{$page_owner->guid}/{$id}",
					));
				}
			}
		}
	}
}
