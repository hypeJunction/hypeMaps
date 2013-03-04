<?php

if (!get_input('__loc_cache', false) && !get_input('__loc')) {
	if (elgg_is_logged_in()) {
		$user = elgg_get_logged_in_user_entity();
		if ($location = $user->getLocation()) {
			set_input('__loc_cache', $location);
			set_input('__rad', 100);
		}
	}
}
$page_owner = elgg_get_page_owner_entity();

$friends = elgg_get_entities_from_relationship(array(
	'types' => 'user',
	'relationship' => 'friend',
	'relationship_guid' => $page_owner->guid,
	'inverse_relationship' => true,
	'limit' => 0
));

if (!$friends) {
	echo '<p>' . elgg_echo('hj:maps:nofriends') . '</p>';
	return true;
}

foreach ($friends as $friend) {
	$owner_guids[] = $friend->guid;
}

$params = array(
	'list_id' => "pl$page_owner->guid",
	'getter_options' => array(
		'type_subtype_pairs' => hj_maps_get_mappable_type_subtype_pairs(),
		'owner_guids' => $owner_guids
	)
);
echo elgg_view('framework/maps/list', $params);