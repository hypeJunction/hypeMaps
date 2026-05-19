<?php

namespace hypeJunction\Maps;

use Elgg\DefaultPluginBootstrap;
use ElggGroup;

/**
 * Bootstrap class.
 */
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
	public function activate(): void {
		$prefix = elgg_get_config('dbprefix');
		elgg()->db->getConnection('write')->executeStatement(
			"CREATE TABLE IF NOT EXISTS {$prefix}geocode_cache (id INT(11) NOT NULL AUTO_INCREMENT, location VARCHAR(255) NOT NULL, lat DECIMAL(10,7) NOT NULL DEFAULT 0, `long` DECIMAL(10,7) NOT NULL DEFAULT 0, PRIMARY KEY (id), UNIQUE KEY location (location)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
		);
	}

	/**
	 * @return void
	 */
	public function init(): void {
		$libs = array_filter((array) elgg_get_config('google_maps_libraries'));
		$gmaps_lib = elgg_http_add_url_query_elements('//maps.googleapis.com/maps/api/js', [
			'key' => elgg_get_plugin_setting('google_api_key', PLUGIN_ID),
			'libraries' => implode(',', $libs),
			'language' => elgg_elgg_get_language(),
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
