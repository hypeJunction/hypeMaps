<?php

$ia = elgg_set_ignore_access(true);
$ha = access_get_show_hidden_status();
access_show_hidden_entities(true);

run_function_once('hj_maps_1361882036');
run_function_once('hj_maps_1362277413');

elgg_set_ignore_access($ia);
access_show_hidden_entities($ha);


function hj_maps_1361882036() {

	$site = elgg_get_site_entity();
	$default_location = elgg_get_plugin_setting('default_location', 'hypeMaps');
	if (!$default_location) {
		$default_location = 'New York City, NY, United States';
	}
	elgg_set_plugin_setting('default_location', $default_location, 'hypeMaps');
	$site->location = $default_location;
	
}

function hj_maps_1362277413() {
	update_subtype('object', 'hjplace', 'hjPlace');
	elgg_set_plugin_setting('metric_system', 'SI', 'hypeMaps');
}