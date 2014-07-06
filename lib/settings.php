<?php

namespace hypeJunction\Maps;

define('HYPEMAPS_RELEASE', 1362277413);

define('HYPEMAPS_METRIC_SYSTEM', elgg_get_plugin_setting('metric_system', PLUGIN_ID));
define('HYPEMAPS_SEARCH_RADIUS', 0);

elgg_set_config('google_maps_libraries', array(
	elgg_get_plugin_setting('adsense_units', PLUGIN_ID) ? 'adsense' : null,
	'drawing',
));
