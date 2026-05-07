<?php

namespace hypeJunction\Maps;

$entity = elgg_extract('entity', $vars);

if ($entity->description) {
	echo '<div>';
	echo elgg_view('output/longtext', [
		'value' => $entity->description,
	]);
	echo '</div>';
}

$src = elgg_http_add_url_query_elements('//maps.googleapis.com/maps/api/staticmap', [
	'center' => $entity->location,
	'zoom' => $entity->zoom,
	'size' => '300x300',
	'markers' => "color:{$entity->pin_color}%7C{$entity->location}",
	'key' => elgg_get_plugin_setting('google_api_key', PLUGIN_ID)
]);

echo '<div>';
echo elgg_view('output/url', [
	'text' => elgg_view('output/img', [
		'src' => $src,
		'width' => 300,
		'height' => 300,
	]),
	'href' => "//maps.google.com/maps?q=$entity->location",
	'target' => '_blank'
]);
echo '</div>';

echo '<div class="pam">';
echo elgg_view('output/maps/location', [
	'value' => $entity->location,
	'class' => 'elgg-text-help',
]);
echo '</div>';
