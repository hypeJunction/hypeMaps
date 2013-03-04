<?php

$group = elgg_get_page_owner_entity();

if ($group->places_enable == "no") {
	return true;
}

$all_link = elgg_view('output/url', array(
	'href' => "places/group/$group->guid",
	'text' => elgg_echo('link:view:all'),
	'is_trusted' => true,
));

elgg_push_context('widgets');

$options = array(
	'types' => 'object',
	'subtypes' => array('hjplace'),
	'container_guids' => $group->guid,
	'full_view' => false,
	'pagination' => false,
);

$content = elgg_list_entities($options);
elgg_pop_context();

if (!$content) {
	$content = '<p>' . elgg_echo('hj:framework:list:empty') . '</p>';
}

$new_link = elgg_view('output/url', array(
	'href' => "maps/create/place/$group->guid",
	'text' => elgg_echo('hj:maps:create:place'),
	'is_trusted' => true,
));

echo elgg_view('groups/profile/module', array(
	'title' => elgg_echo('hj:maps:places:groups'),
	'content' => $content,
	'all_link' => $all_link,
	'add_link' => $new_link,
));
