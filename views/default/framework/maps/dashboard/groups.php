<?php

$page_owner = elgg_get_page_owner_entity();

$groups = elgg_get_entities_from_relationship(array(
	'types' => 'group',
	'relationship' => 'member',
	'relationship_guid' => $page_owner->guid,
	'limit' => 0
		));

if (!$groups) {
	echo '<p>' . elgg_echo('hj:maps:nogroups') . '</p>';
	return true;
}

foreach ($groups as $group) {
	$group_guids[] = $group->guid;
}

$params = array(
	'list_id' => "grp$page_owner->guid",
	'getter_options' => array(
		'type_subtype_pairs' => hj_maps_get_mappable_type_subtype_pairs(),
		'container_guids' => $group_guids
	)
);
echo elgg_view('framework/maps/list', $params);