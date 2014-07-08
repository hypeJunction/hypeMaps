<?php

/**
 * hypeMaps
 *
 * Maps UI
 * @package hypeJunction
 * @subpackage Maps
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 * @copyright Copyrigh (c) 2011-2013, Ismayil Khayredinov
 */

namespace hypeJunction\Maps;

use ElggGroup;

const PLUGIN_ID = 'hypeMaps';
const PAGEHANDLER = 'maps';

require_once __DIR__ . '/vendors/autoload.php';

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/settings.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/page_handlers.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');
elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init_groups');
elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\pagesetup');
elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\pagesetup_groups');

function init() {

	/**
	 * Pages and URLs
	 */
	elgg_register_page_handler(PAGEHANDLER, __NAMESPACE__ . '\\page_handler');

	/**
	 * Actions
	 */
	elgg_register_action(PLUGIN_ID . '/settings/save', __DIR__ . '/actions/settings/maps.php', 'admin');
	elgg_register_action('maps/geopositioning/update', __DIR__ . '/actions/geopositioning/update.php', 'public');

	/**
	 * JS and CSS
	 */
	$libs = array_filter(elgg_get_config('google_maps_libraries'));
	$gmaps_lib = elgg_http_add_url_query_elements('//maps.googleapis.com/maps/api/js', array(
		'key' => elgg_get_plugin_setting('google_api_key', PLUGIN_ID),
		'libraries' => implode(',', $libs),
		'language' => get_current_language(),
		'output' => 'svembed',
	));
	elgg_register_js('google.maps', $gmaps_lib);

	elgg_register_simplecache_view('css/framework/maps/stylesheet');
	elgg_register_css('maps', elgg_get_simplecache_url('css', 'framework/maps/stylesheet'));

	elgg_register_js('jquery.sticky-kit', '/mod/' . PLUGIN_ID . '/vendors/sticky-kit/jquery.sticky-kit.min.js', 'footer', 500);

	elgg_register_simplecache_view('js/framework/maps/mapbox');
	elgg_register_js('maps.mapbox', elgg_get_simplecache_url('js', 'framework/maps/mapbox'), 'footer', 550);

	// Add User Location to config
	elgg_extend_view('js/initialize_elgg', 'js/framework/maps/config');

	/**
	 * Hooks
	 */
	elgg_register_plugin_hook_handler('search:site', 'maps', __NAMESPACE__ . '\\setup_site_search_maps');

	// Replace a list with a map when ?list_type=mapbox
	elgg_register_plugin_hook_handler('view', 'page/components/list', __NAMESPACE__ . '\\list_type_map_view');
	elgg_register_plugin_hook_handler('view', 'page/components/gallery', __NAMESPACE__ . '\\list_type_map_view');

	// Filter out views when loading map items via ajax
	elgg_register_plugin_hook_handler('view', 'all', __NAMESPACE__ . '\\ajax_list_view');

	// Map Markers
	elgg_register_plugin_hook_handler('entity:icon:url', 'user', __NAMESPACE__ . '\\get_marker_url', 600);
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', __NAMESPACE__ . '\\get_marker_url', 600);

	elgg_register_widget_type('staticmap', elgg_echo('maps:widget:staticmap'), elgg_echo('maps:widget:staticmap:desc'), 'all', true);
}

function init_groups() {

	elgg_register_plugin_hook_handler('entity:icon:url', 'group', __NAMESPACE__ . '\\get_marker_url', 600);
	elgg_register_plugin_hook_handler('search:group', 'maps', __NAMESPACE__ . '\\setup_group_search_maps');

	$group_maps = get_group_search_maps(new ElggGroup);
	if (is_array($group_maps)) {
		foreach ($group_maps as $id => $gm) {
			add_group_tool_option("maps_$id", elgg_echo("maps:groupoption:$id:enable"), true);
			//elgg_extend_view('groups/tool_latest', "framework/maps/group/$id");
		}
	}
}
