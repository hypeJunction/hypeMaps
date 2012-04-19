<?php

$user = elgg_get_logged_in_user_entity();

$clat = get_input('clat');
$clong = get_input('clong');

$address = get_input('location');

$location = new hjLocation();
$latlong = $location->getGeoCodedAddress($address);

if ($clat && $clong) {
    $params = array(
        'clat' => $clat,
        'clong' => $clong,
        'useSessionLocation' => false
    );
} else {
    $params = array(
        'clat' => $latlong->lat(),
        'clong' => $latlong->long(),
        'useSessionLocation' => false
    );
}

$map = elgg_view_entity_list(array(), array(
	'list_type' => 'geomap',
	'list_class' => 'hj-geomap-list',
	'autorefresh' => false,
	'class' => 'hj-view-list',
	'list_id' => 'hj-map-popup',
	'map_params' => $params
));

echo $map;

if (elgg_is_xhr()) {
    return true;
} else {
    forward(REFERER);
}
