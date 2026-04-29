<?php

namespace hypeJunction\Maps;

use Elgg\DefaultPluginBootstrap;
use ElggGroup;

class Bootstrap extends DefaultPluginBootstrap {

	/**
     * @return void
     */
    public function load(): void {
		define('hypeJunction\Maps\PLUGIN_ID', 'hypemaps');
		define('hypeJunction\Maps\PAGEHANDLER', 'maps');

		require_once $this->plugin->getPath() . 'lib/functions.php';
		require_once $this->plugin->getPath() . 'lib/hooks.php';
		require_once $this->plugin->getPath() . 'lib/events.php';

		define('HYPEMAPS_RELEASE', 1362277413);
		define('HYPEMAPS_METRIC_SYSTEM', elgg_get_plugin_setting('metric_system', PLUGIN_ID));
		define('HYPEMAPS_SEARCH_RADIUS', 0);

		elgg_set_config('google_maps_libraries', array_filter([
			elgg_get_plugin_setting('adsense_units', PLUGIN_ID) ? 'adsense' : null,
			'drawing',
		]));
	}

	/**
     * @return void
     */
    public function init(): void {
		$libs = array_filter((array) elgg_get_config('google_maps_libraries'));
		$gmaps_lib = elgg_http_add_url_query_elements('//maps.googleapis.com/maps/api/js', [
			'key' => elgg_get_plugin_setting('google_api_key', PLUGIN_ID),
			'libraries' => implode(',', $libs),
			'language' => get_current_language(),
			'output' => 'svembed',
		]);
		elgg_register_external_file('js', 'google.maps', $gmaps_lib);

		elgg_register_external_file('css', 'maps', elgg_get_simplecache_url('css/framework/maps/stylesheet'));

		$path = '/mod/' . PLUGIN_ID;
		elgg_register_external_file('js', 'jquery.sticky-kit', $path . '/vendor/bower-asset/sticky-kit/jquery.sticky-kit.min.js', 'footer');
		elgg_register_external_file('js', 'maps.mapbox', elgg_get_simplecache_url('js/framework/maps/mapbox'), 'footer');

		elgg_extend_view('js/initialize_elgg', 'js/framework/maps/config');

		$group_maps = get_group_search_maps(new ElggGroup());
		if (is_array($group_maps)) {
			foreach ($group_maps as $id => $gm) {
				elgg()->group_tools->register("maps_{$id}", [
					'label' => elgg_echo("maps:groupoption:{$id}:enable"),
					'default_on' => true,
				]);
			}
		}
	}
}
