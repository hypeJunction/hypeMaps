<?php

$user = elgg_get_logged_in_user_entity();
$temp = new hjEntityLocation($user->guid);
$user_position = $temp->getMapParams();

$clat = get_input('clat');
$clong = get_input('clong');
$useSessionLocation = get_input('sl', null);

$guids = get_input('e');
$guids = explode(',', $guids);

foreach ($guids as $guid) {
    $marker = new hjEntityLocation($guid);
    $markers[] = $marker->getMapParams();
}

if ($clat && $clong) {
    $center = array(
        'latitude' => $clat,
        'longitude' => $clong,
        'id' => rand(0, 100)
    );
} else if (sizeof($markers) == 1) {
    $entity = $markers[0];
    $center = array(
        'latitude' => $entity['location']['latitude'],
        'longitude' => $entity['location']['longitude'],
        'id' => $entity['entity']['guid']
    );
} else {
    $center = array(
        'latitude' => $user_position['location']['latitude'],
        'longitude' => $user_position['location']['longitude'],
        'id' => $user_position['entity']['guid'],
        'useSessionLocation' => $useSessionLocation
    );
}


$output = array('center' => $center, 'markers' => $markers, 'user' => $user_position);

print(json_encode($output));
if (elgg_is_xhr()) {
    return true;
} else {
    forward(REFERER);
}