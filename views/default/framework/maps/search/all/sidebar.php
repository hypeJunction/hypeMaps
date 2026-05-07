<?php

namespace hypeJunction\Maps;

echo elgg_view_form('maps/filter/_default', [
	'action' => current_page_url(),
	'method' => 'GET',
	'disable_security' => true,
	'class' => 'maps-filter'
], $vars);

if (elgg_get_plugin_setting('search_users', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/users', [
		'action' => PAGEHANDLER . '/search/users',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
	], $vars);
}

if (elgg_get_plugin_setting('search_friends', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/friends', [
		'action' => PAGEHANDLER . '/search/friends',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
	], $vars);
}

if (elgg_get_plugin_setting('search_groups', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/groups', [
		'action' => PAGEHANDLER . '/search/groups',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
	], $vars);
}

if (elgg_get_plugin_setting('search_objects', PLUGIN_ID)) {
	echo elgg_view_form('maps/filter/objects', [
		'action' => PAGEHANDLER . '/search/objects',
		'method' => 'GET',
		'disable_security' => true,
		'class' => 'maps-filter'
	], $vars);
}
