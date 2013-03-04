<?php

$page_owner = elgg_get_page_owner_entity();

$params = array(
	'list_id' => "gr$page_owner->guid",
	'getter_options' => array(
		'type_subtype_pairs' => hj_maps_get_mappable_type_subtype_pairs(),
		'container_guids' => $page_owner->guid
	)
);
echo elgg_view('framework/maps/list', $params);