<?php

$location_label = elgg_echo('hj:maps:setdefaultlocation');
$location_input = elgg_view('input/text', array(
	'name' => 'temp_location',
	'value' => $vars['address'],
	'class' => 'hj-location-autocomplete',
	'id' => 'hj-location-setdefaultlocation'
));
$location_submit = elgg_view('input/submit', array(
	'value' => elgg_echo('save')
));

$form_body = elgg_view_layout('hj/dynamic', array(
	'grid' => array(2,8,2),
	'content' => array($location_label, $location_input, $location_submit)
));

echo elgg_view('input/form', array(
	'action' => '',
	'body' => $form_body,
	'id' => 'hj-maps-change-default-location',
	'rel' => $vars['rel']
));
