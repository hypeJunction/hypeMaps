<?php

$user = elgg_get_logged_in_user_entity();
$temp = new hjEntityLocation($user->guid);
$user_position = $temp->getMapParams();

$clat = get_input('clat');
$clong = get_input('clong');
$useSessionLocation = get_input('useSessionLocation', true);

$guids = get_input('e');
$guids = explode(',', $guids);

foreach ($guids as $guid) {
    $entities[] = get_entity($guid);
}

$map = elgg_view_entity_list($entities, array(
	'list_type' => 'geomap',
	'list_class' => 'hj-geomap-list',
	'autorefresh' => false,
	'class' => 'hj-view-list',
	'list_id' => 'hj-map-popup',
	'map_params' => array('useSessionLocation' => false)
));

echo $map;

if (elgg_is_xhr()) {
    return true;
} else {
    forward(REFERER);
}