<?php

/* hypeMaps
 *
 * Maps and Places
 * @package hypeJunction
 * @subpackage hypeMaps
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 * @copyright Copyrigh (c) 2011-2013, Ismayil Khayredinov
 */

define('HYPEMAPS_RELEASE', 1362277413);

define('HYPEMAPS_INTERFACE_LOCATION', elgg_get_plugin_setting('interface_location', 'hypeMaps'));
define('HYPEMAPS_INTERFACE_PLACES', elgg_get_plugin_setting('interface_places', 'hypeMaps'));
define('HYPEMAPS_INTERFACE_VICINITY', elgg_get_plugin_setting('interface_vicinity', 'hypeMaps'));
define('HYPEMAPS_METRIC_SYSTEM', elgg_get_plugin_setting('metric_system', 'hypeMaps'));
define('HYPEMAPS_PLACES_RIVER', elgg_get_plugin_setting('places_river', 'hypeMaps'));
define('HYPEMAPS_PLACES_COVER', elgg_get_plugin_setting('places_cover', 'hypeMaps'));
define('HYPEMAPS_SUMMARY_MAP', elgg_get_plugin_setting('summary_map', 'hypeMaps'));
define('HYPEMAPS_LINK_MAP', elgg_get_plugin_setting('link_map', 'hypeMaps'));
define('HYPEMAPS_GROUP_PLACES', elgg_get_plugin_setting('group_places', 'hypeMaps'));

elgg_register_event_handler('init', 'system', 'hj_maps_init');

function hj_maps_init() {

	$plugin = 'hypeMaps';

	// Make sure hypeFramework is active and precedes hypeMaps in the plugin list
	if (!is_callable('hj_framework_path_shortcuts')) {
		register_error(elgg_echo('framework:error:plugin_order', array($plugin)));
		disable_plugin($plugin);
		forward('admin/plugins');
	}

	// Run upgrade scripts
	hj_framework_check_release($plugin, HYPEMAPS_RELEASE);

	$shortcuts = hj_framework_path_shortcuts($plugin);

	// Helper Classes
	elgg_register_classes($shortcuts['classes']);

	// Libraries
	$libraries = array(
		'base',
		'forms',
		'page_handlers',
		'actions',
		'assets',
		'views',
		'menus',
		'hooks',
		'views'
	);

	if (!HYPEFRAMEWORK_INTERFACE_LOCATION) {
		$libraries[] = 'location';
	}

	foreach ($libraries as $lib) {
		$path = "{$shortcuts['lib']}{$lib}.php";
		if (file_exists($path)) {
			elgg_register_library("maps:library:$lib", $path);
			elgg_load_library("maps:library:$lib");
		}
	}

	hj_maps_define_default_map_center();

	// Search
	elgg_register_entity_type('object', 'hjplace');

	elgg_register_tag_metadata_name('location');

	// Add group option
	if (HYPEMAPS_GROUP_PLACES) {
		add_group_tool_option('places', elgg_echo('hj:maps:groupoption:enable'), true);
		elgg_extend_view('groups/tool_latest', 'framework/maps/group_module');
	}
}
