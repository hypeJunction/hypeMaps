<?php

namespace hypeJunction\Maps;

use ElggBatch;

$query = get_input('query', []);
$location = get_input('location', []);
$radius = get_input('radius', HYPEMAPS_SEARCH_RADIUS);

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:attributes') . '</label>';
$body .= elgg_view('input/text', [
	'name' => 'query[group]',
	'value' => elgg_extract('group', $query)
]);
$body .= '</div>';

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:tags') . '</label>';
$body .= elgg_view('input/text', [
	'name' => 'query[tags]',
	'value' => elgg_extract('tags', $query)
]);
$body .= '</div>';

$user = elgg_get_logged_in_user_entity();

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:location') . '</label>';

if ($user) {
	$options = [
		'owner_guids' => $user->guid,
		'metadata_names' => ['location', 'temp_location'],
		'limit' => 0,
		'group_by' => 'n_table.value',
		'wheres' => ["n_table.value != '' AND n_table.value != '0,0'"],
		'order_by' => 'n_table.value ASC'
	];

	$metadata = new ElggBatch('elgg_get_metadata', $options);
	foreach ($metadata as $md) {
		$locations[] = $md->value;
	}

	if (count($locations)) {
		array_unshift($locations, elgg_echo('maps:filter:location:change'));
		$body .= '<div class="maps-filter-location-cache">';
		$body .= elgg_view('input/dropdown', [
			'name' => 'location[cached]',
			'value' => elgg_extract('cached', $location),
			'options' => $locations
		]);
		$body .= '</div>';
	}
}

$body .= '<div class="maps-filter-location">';
$body .= elgg_view('input/location', [
	'name' => 'location[find]',
	'value' => elgg_extract('find', $location),
]);
$body .= '</div>';
$body .= '</div>';

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:radius') . '</label>';
$body .= '<div class="maps-filter-radius">';
$key = 'maps:proximity:' . HYPEMAPS_METRIC_SYSTEM;
$body .= elgg_view('input/dropdown', [
	'name' => 'radius',
	'value' => $radius,
	'options_values' => [
		0 => elgg_echo('maps:filter:radius:none'),
		5 => elgg_echo($key, [5]),
		10 => elgg_echo($key, [10]),
		25 => elgg_echo($key, [25]),
		100 => elgg_echo($key, [100]),
		500 => elgg_echo($key, [500])
	]
]);
$body .= '</div>';
$body .= '</div>';

$footer .= elgg_view('input/submit', [
	'value' => elgg_echo('filter'),
]);

echo elgg_view_module('aside', elgg_echo('maps:filter:groups'), $body, [
	'footer' => $footer
]);
