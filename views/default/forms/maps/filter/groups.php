<?php

namespace hypeJunction\Maps;

use ElggBatch;

$query = get_input('query', array());
$location = get_input('location', array());
$radius = get_input('radius', HYPEMAPS_SEARCH_RADIUS);

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:attributes') . '</label>';
$body .= elgg_view('input/text', array(
	'name' => 'query[group]',
	'value' => elgg_extract('group', $query)
		));
$body .= '</div>';

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:tags') . '</label>';
$body .= elgg_view('input/text', array(
	'name' => 'query[tags]',
	'value' => elgg_extract('tags', $query)
		));
$body .= '</div>';

$user = elgg_get_logged_in_user_entity();

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:location') . '</label>';

if ($user) {
	$options = array(
		'owner_guids' => $user->guid,
		'metadata_names' => array('location', 'temp_location'),
		'limit' => 0,
		'group_by' => 'v.string',
		'wheres' => array("v.string != '' AND v.string != '0,0'"),
		'order_by' => 'v.string ASC'
	);

	$metadata = new ElggBatch('elgg_get_metadata', $options);
	foreach ($metadata as $md) {
		$locations[] = $md->value;
	}

	if (count($locations)) {
		array_unshift($locations, elgg_echo('maps:filter:location:change'));
		$body .= '<div class="maps-filter-location-cache">';
		$body .= elgg_view('input/dropdown', array(
			'name' => 'location[cached]',
			'value' => elgg_extract('cached', $location),
			'options' => $locations
		));
		$body .= '</div>';
	}
}

$body .= '<div class="maps-filter-location">';
$body .= elgg_view('input/location', array(
	'name' => 'location[find]',
	'value' => elgg_extract('find', $location),
		));
$body .= '</div>';
$body .= '</div>';

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:groups:radius') . '</label>';
$body .= '<div class="maps-filter-radius">';
$key = 'maps:proximity:' . HYPEMAPS_METRIC_SYSTEM;
$body .= elgg_view('input/dropdown', array(
	'name' => 'radius',
	'value' => $radius,
	'options_values' => array(
		0 => elgg_echo('maps:filter:radius:none'),
		5 => elgg_echo($key, array(5)),
		10 => elgg_echo($key, array(10)),
		25 => elgg_echo($key, array(25)),
		100 => elgg_echo($key, array(100)),
		500 => elgg_echo($key, array(500))
	)
		));
$body .= '</div>';
$body .= '</div>';

$footer .= elgg_view('input/submit', array(
	'value' => elgg_echo('filter'),
		));

echo elgg_view_module('aside', elgg_echo('maps:filter:groups'), $body, array(
	'footer' => $footer
		));