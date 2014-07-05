<?php

namespace hypeJunction\Maps;

echo elgg_view_form('maps/filter/_default', array(
	'action' => current_page_url(),
	'method' => 'GET',
	'disable_security' => true,
	'class' => 'maps-filter'
		), $vars);

if (elgg_get_plugin_setting('search_users', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/users', array(
		'action' => PAGEHANDLER . '/search/users',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
			), $vars);
}

if (elgg_get_plugin_setting('search_friends', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/friends', array(
		'action' => PAGEHANDLER . '/search/friends',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
			), $vars);
}

if (elgg_get_plugin_setting('search_groups', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/groups', array(
		'action' => PAGEHANDLER . '/search/groups',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
			), $vars);
}

if (elgg_get_plugin_setting('search_objects', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/objects', array(
		'action' => PAGEHANDLER . '/search/objects',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
			), $vars);
}