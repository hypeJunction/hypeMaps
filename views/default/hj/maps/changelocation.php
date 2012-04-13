<?php

$location_label = elgg_echo('hj:maps:searchlocation');
$location_input = elgg_view('input/text', array(
	'name' => 'address',
	'value' => $vars['address'],
	'class' => 'hj-location-autocomplete',
	'id' => 'hj-location-searchlocation'
));
$location_submit = elgg_view('input/submit', array(
	'value' => elgg_echo('change')
));

$form_body = elgg_view_layout('hj/dynamic', array(
	'grid' => array(2,8,2),
	'content' => array($location_label, $location_input, $location_submit)
));

echo elgg_view('input/form', array(
	'action' => '',
	'body' => $form_body,
	'id' => 'hj-maps-change-session-location',
	'rel' => $vars['rel']
));
