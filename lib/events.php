<?php

namespace hypeJunction\Maps;

use Elgg\Hook;

/**
 * @param Hook $hook
 * @return mixed
 */
function register_site_menu(Hook $hook) {
	$return = $hook->getValue();
	$return[] = \ElggMenuItem::factory([
		'name' => 'maps',
		'text' => \elgg_echo('maps'),
		'href' => 'maps',
	]);
	return $return;
}

/**
 * @param Hook $hook
 * @return mixed
 */
function register_owner_block_menu(Hook $hook) {
	$page_owner = $hook->getEntityParam();
	if (!$page_owner instanceof \ElggGroup) {
		return;
	}

	$group_maps = get_group_search_maps($page_owner);
	if (!is_array($group_maps)) {
		return;
	}

	$return = $hook->getValue();
	foreach ($group_maps as $id => $gm) {
		$groupoption = "maps_{$id}_enable";
		if ($page_owner->$groupoption === 'no') {
			continue;
		}
		$return[] = \ElggMenuItem::factory([
			'name' => "maps:$id",
			'text' => \elgg_extract('title', $gm),
			'href' => "maps/group/{$page_owner->guid}/{$id}",
		]);
	}

	return $return;
}
