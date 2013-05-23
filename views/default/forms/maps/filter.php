<?php

$user = elgg_get_logged_in_user_entity();

if (!$user) {
	return true;
}

$options = array(
	'owner_guids' => $user->guid,
	'metadata_names' => array('location', 'temp_location'),
	'limit' => 0,
	'group_by' => 'v.string',
	'wheres' => array("v.string != '' AND v.string != '0,0'"),
	'order_by' => 'v.string ASC'
);

$metadata = elgg_get_metadata($options);
$metadata2 = $user->temp_location;

if ($metadata) {
	foreach ($metadata as $md) {
		if (!is_array($md->value)) {
			$dd_options[] = $md->value;
		}
	}
	if (is_array($metadata2)) {
		foreach ($metadata2 as $md) {
			$dd_options[] = $md;
		}
	}
	$dd_options = array_unique($dd_options);
	asort($dd_options);
	array_unshift($dd_options, elgg_echo('hj:maps:filter:location:change'));

		$body .= '<div class="hj-maps-filter-location-cache">';
	//$body .= '<label>' . elgg_echo('hj:maps:filter:mylocations') . '</label>';
	$body .= elgg_view('input/dropdown', array(
		'name' => '__loc_cache',
		'value' => urldecode(get_input('__loc_cache', false)),
		'options' => $dd_options
			));
	$body .= '</div>';
}

$body .= '<div class="hj-maps-filter-location">';
//$body .= '<label>' . elgg_echo('hj:maps:filter:searchlocation') . '</label>';
$body .= elgg_view('input/location', array(
	'name' => '__loc',
	'placeholder' => elgg_echo('hj:maps:filter:location:find')
		));
$body .= '</div>';

$body .= '<div class="hj-maps-filter-radius">';
$body .= elgg_view('input/dropdown', array(
	'name' => '__rad',
	'value' => get_input('__rad', 500),
	'options_values' => array(
		0 => elgg_echo('hj:maps:filter:radius:change'),
		5 => elgg_echo('hj:maps:radius:' . HYPEMAPS_METRIC_SYSTEM, array(5)),
		10 => elgg_echo('hj:maps:radius:' . HYPEMAPS_METRIC_SYSTEM, array(10)),
		25 => elgg_echo('hj:maps:radius:' . HYPEMAPS_METRIC_SYSTEM, array(25)),
		100 => elgg_echo('hj:maps:radius:' . HYPEMAPS_METRIC_SYSTEM, array(100)),
		500 => elgg_echo('hj:maps:radius:' . HYPEMAPS_METRIC_SYSTEM, array(500))
	)
));
$body .= '</div>';

// Loader placeholder
$footer .= '<div class="float">';
$footer .= '<div class="hj-ajax-loader hj-loader-indicator hidden"></div>';
$footer .= '</div>';

$footer .= '<div class="float">';
$footer .= elgg_view('input/submit', array(
	'value' => elgg_echo('search')
));
$footer .= '</div>';

echo elgg_view_module('form', '', $body, array('footer' => $footer));