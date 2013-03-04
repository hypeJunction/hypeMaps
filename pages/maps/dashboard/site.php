<?php

hj_maps_register_dashboard_title_buttons('site');

$title = elgg_echo('hj:maps:vicinity');

elgg_push_breadcrumb($title);

$filter = elgg_view('framework/maps/dashboard/filter', array(
	'filter_context' => 'site'
));

$content = elgg_view('framework/maps/dashboard/site');

$sidebar = elgg_view('framework/maps/dashboard/sidebar', array(
	'dashboard' => 'site'
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'filter' => $filter,
	'content' => $content,
	'sidebar' => $sidebar,
	'class' => 'hj-maps-dashboard'
));

echo elgg_view_page($title, $layout);