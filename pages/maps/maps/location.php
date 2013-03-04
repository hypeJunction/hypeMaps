<?php

$location = urldecode(get_input('location'));
$guid = get_input('guid');
$entity = get_entity($guid);

if ($entity) {
	if ($entity->title) {
		elgg_push_breadcrumb($entity->title, $entity->getURL());
		$title = $entity->title;
	} else if ($entity->name) {
		elgg_push_breadcrumb($entity->name, $entity->getURL());
		$title = $entity->name;
	}
} else {
	$title = elgg_echo('hj:maps:showmap');
	$title = "$title: $location";
	elgg_push_breadcrumb($title);
}

$content = elgg_view('framework/maps/maps/location', array(
	'location' => $location,
	'entity' => $entity
		));

$layout = elgg_view_layout('one_sidebar', array(
	'title' => $title,
	'content' => $content,
		));

echo elgg_view_page($title, $layout);
