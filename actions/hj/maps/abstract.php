<?php

$user = elgg_get_logged_in_user_entity();

$clat = get_input('clat');
$clong = get_input('clong');

$address = get_input('location');

$location = new hjLocation();
$latlong = $location->getGeoCodedAddress($address);

if ($clat && $clong) {
    $center = array(
        'latitude' => $clat,
        'longitude' => $clong,
        'id' => rand(0, 100)
    );
} else {
    $center = array(
        'latitude' => $latlong->lat(),
        'longitude' => $latlong->long(),
        'id' => rand(0, 100)
    );
}

$output = array('center' => $center);

print(json_encode($output));
if (elgg_is_xhr()) {
    return true;
} else {
    forward(REFERER);
}
