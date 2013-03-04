<?php

elgg_push_context('groups');

hj_maps_register_dashboard_title_buttons('groups');

$title = elgg_echo('hj:maps:groups');

elgg_push_breadcrumb($title);

$filter = elgg_view('framework/maps/dashboard/filter', array(
	'filter_context' => 'groups'
		));

$content = elgg_view('framework/maps/dashboard/groups');

$sidebar = elgg_view('framework/maps/dashboard/sidebar', array(
	'dashboard' => 'groups'
		));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'filter' => $filter,
	'content' => $content,
	'sidebar' => $sidebar,
	'class' => 'hj-maps-dashboard'
		));

echo elgg_view_page($title, $layout);

elgg_pop_context();