<?php

$params = get_input('params');
$plugin_id = get_input('plugin_id');
$plugin = elgg_get_plugin_from_id($plugin_id);

if (!($plugin instanceof ElggPlugin)) {
	elgg_register_error_message(elgg_echo('plugins:settings:save:fail', array($plugin_id)));
	forward(REFERER);
}

$plugin_name = $plugin->getManifest()->getName();

$result = false;

foreach ($params as $k => $v) {
	if (is_array($v)) {
		$v = json_encode($v);
	}
	$result = $plugin->setSetting($k, $v);
	if (!$result) {
		elgg_register_error_message(elgg_echo('plugins:settings:save:fail', array($plugin_name)));
		forward(REFERER);
		exit;
	}
}

$site = elgg_get_site_entity();
$site->default_location = $params['default_location'];
$site->location = $site->default_location;

elgg_register_success_message(elgg_echo('plugins:settings:save:ok', array($plugin_name)));
forward(REFERER);