<?php

$params = get_input('params');
$plugin_id = get_input('plugin_id');
$plugin = elgg_get_plugin_from_id($plugin_id);

if (!($plugin instanceof ElggPlugin)) {
	return elgg_error_response(elgg_echo('plugins:settings:save:fail', [$plugin_id]), REFERER);
}

$plugin_name = $plugin->getDisplayName();

foreach ((array) $params as $k => $v) {
	if (is_array($v)) {
		$v = json_encode($v);
	}

	if (!$plugin->setSetting($k, $v)) {
		return elgg_error_response(elgg_echo('plugins:settings:save:fail', [$plugin_name]), REFERER);
	}
}

$site = elgg_get_site_entity();
$site->default_location = $params['default_location'];
$site->location = $site->default_location;

return elgg_ok_response('', elgg_echo('plugins:settings:save:ok', [$plugin_name]), REFERER);
