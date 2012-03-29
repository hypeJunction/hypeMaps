<?php

function hj_maps_setup() {
	if (elgg_is_logged_in()) {
		elgg_set_plugin_setting('default_location', 'New York, NY, United States', 'hypeMaps');
		$site = elgg_get_site_entity();
		$site->default_location = $params['default_location'];
		$location = new hjEntityLocation($site->default_location);
		$location->setAddressMetadata($site->default_location);
		$location->setEntityLocation($site->default_location);

		elgg_set_plugin_setting('default_zoom', '15', 'hypeMaps');

		elgg_set_plugin_setting('hj:maps:setup', true);
		return true;
	}
	return false;
}
