<?php
gatekeeper();

$guid = get_input('e');
$entity = get_entity($guid);

if (!$entity) {
    exit;
}

$rec = get_input('rec');

$location = get_input('location');

$temp_location = get_input('temp_location');

$session_latitude = get_input('session_latitude');
$session_longitude = get_input('session_longitude');

if ($location) {
    $ent = new hjEntityLocation($entity->guid);
    $ent->setEntityLocation($location);
}

if ($temp_location) {
    $ent = new hjEntityLocation($entity->guid);
    $ent->setEntityTempLocation($temp_location);
}

if ($rec == 'reset_temp') {
    $entity->temp_location = null;
    $entity->temp_latitude = null;
    $entity->temp_longitude = null;
}
if ($session_latitude && $session_longitude) {
    $entloc = new hjLocation;
    $latlong = new hjLatLong($session_latitude, $session_longitude);
    $_SESSION['location'] = $entloc->getReverseGeoCode($latlong);
    $_SESSION['latitude'] = $latlong->lat();
    $_SESSION['longitude'] = $latlong->long();
}

if (elgg_is_xhr()) {
    return true;
} else {
    forward(REFERER);
}