<?php

namespace hypeJunction\Maps;

if ($group->maps_group_members_enable == "no") {
	return true;
}

$group = elgg_get_page_owner_entity();
$maps = get_group_search_maps($group);

if (!isset($maps['group_members'])) {
	return true;
}

elgg_push_context('widgets');
$params = $maps['group_members'];
$content = ElggMap::showMap($params);
elgg_pop_context();

echo elgg_view('groups/profile/module', array(
	'title' => elgg_echo('maps:module:group_members'),
	'content' => $content,
));
