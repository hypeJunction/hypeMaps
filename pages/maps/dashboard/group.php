<?php

hj_maps_register_dashboard_title_buttons('group');

$group = elgg_get_page_owner_entity();
$title = elgg_echo('hj:maps:group', array($group->name));

elgg_push_breadcrumb($group->name, "maps/group/$group->guid");

$content = elgg_view('framework/maps/dashboard/group');

$sidebar = elgg_view('framework/maps/dashboard/sidebar', array(
	'dashboard' => 'group'
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'filter' => false,
	'content' => $content,
	'sidebar' => $sidebar,
	'class' => 'hj-maps-dashboard'
));

echo elgg_view_page($title, $layout);