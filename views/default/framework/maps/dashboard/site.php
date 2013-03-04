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

$params = array(
	'list_id' => "plsite",
	'getter_options' => array(
		'type_subtype_pairs' => hj_maps_get_mappable_type_subtype_pairs()
	)
);
echo elgg_view('framework/maps/list', $params);