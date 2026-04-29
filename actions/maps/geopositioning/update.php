<?php

namespace hypeJunction\Maps;

$location = get_input('location');
$latitude = get_input('latitude');
$longitude = get_input('longitude');

if ($location) {
	set_geopositioning($location, $latitude, $longitude);
}

return elgg_ok_response('', '', REFERER);