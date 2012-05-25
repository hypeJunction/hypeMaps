<?php

function hj_places_get_marker_types() {
	$marker_types = array(
		"default" => elgg_echo("markertype:value:default"),
		"airport" => elgg_echo("markertype:value:airport"),
		"amusement_park" => elgg_echo("markertype:value:amusement_park"),
		"aquarium" => elgg_echo("markertype:value:aquarium"),
		"art_gallery" => elgg_echo("markertype:value:art_gallery"),
		"atm" => elgg_echo("markertype:value:atm"),
		"bakery" => elgg_echo("markertype:value:bakery"),
		"bank" => elgg_echo("markertype:value:bank"),
		"bar" => elgg_echo("markertype:value:bar"),
		"beauty_salon" => elgg_echo("markertype:value:beauty_salon"),
		"bicycle_store" => elgg_echo("markertype:value:bicycle_store"),
		"bowling_alley" => elgg_echo("markertype:value:bowling_alley"),
		"bus_station" => elgg_echo("markertype:value:bus_station"),
		"cafe" => elgg_echo("markertype:value:cafe"),
		"campground" => elgg_echo("markertype:value:campground"),
		"car_dealer" => elgg_echo("markertype:value:car_dealer"),
		"car_rental" => elgg_echo("markertype:value:car_rental"),
		"car_repair" => elgg_echo("markertype:value:car_repair"),
		"car_wash" => elgg_echo("markertype:value:car_wash"),
		"casino" => elgg_echo("markertype:value:casino"),
		"cemetery" => elgg_echo("markertype:value:cemetery"),
		"church" => elgg_echo("markertype:value:church"),
		"city_hall" => elgg_echo("markertype:value:city_hall"),
		"clothing_store" => elgg_echo("markertype:value:clothing_store"),
		"convenience_store" => elgg_echo("markertype:value:convenience_store"),
		"courthouse" => elgg_echo("markertype:value:courthouse"),
		"dentist" => elgg_echo("markertype:value:dentist"),
		"department_store" => elgg_echo("markertype:value:department_store"),
		"electrician" => elgg_echo("markertype:value:electrician"),
		"electronics_store" => elgg_echo("markertype:value:electronics_store"),
		"embassy" => elgg_echo("markertype:value:embassy"),
		"finance" => elgg_echo("markertype:value:finance"),
		"fire_station" => elgg_echo("markertype:value:fire_station"),
		"florist" => elgg_echo("markertype:value:florist"),
		"food" => elgg_echo("markertype:value:food"),
		"furniture_store" => elgg_echo("markertype:value:furniture_store"),
		"gas_station" => elgg_echo("markertype:value:gas_station"),
		"government_office" => elgg_echo("markertype:value:government_office"),
		"gym" => elgg_echo("markertype:value:gym"),
		"hardware_store" => elgg_echo("markertype:value:hardware_store"),
		"hospital" => elgg_echo("markertype:value:hospital"),
		"jewelry_store" => elgg_echo("markertype:value:jewelry_store"),
		"laundry" => elgg_echo("markertype:value:laundry"),
		"lawyer" => elgg_echo("markertype:value:lawyer"),
		"library" => elgg_echo("markertype:value:library"),
		"liquor_store" => elgg_echo("markertype:value:liquor_store"),
		"lodging" => elgg_echo("markertype:value:lodging"),
		"mosque" => elgg_echo("markertype:value:mosque"),
		"movie_rental" => elgg_echo("markertype:value:movie_rental"),
		"movie_theater" => elgg_echo("markertype:value:movie_theater"),
		"museum" => elgg_echo("markertype:value:museum"),
		"night_club" => elgg_echo("markertype:value:night_club"),
		"park" => elgg_echo("markertype:value:park"),
		"parking" => elgg_echo("markertype:value:parking"),
		"pharmacy" => elgg_echo("markertype:value:pharmacy"),
		"police" => elgg_echo("markertype:value:police"),
		"post_office" => elgg_echo("markertype:value:post_office"),
		"restaurant" => elgg_echo("markertype:value:restaurant"),
		"school" => elgg_echo("markertype:value:school"),
		"shoe_store" => elgg_echo("markertype:value:shoe_store"),
		"shopping_mall" => elgg_echo("markertype:value:shopping_mall"),
		"spa" => elgg_echo("markertype:value:spa"),
		"stadium" => elgg_echo("markertype:value:stadium"),
		"store" => elgg_echo("markertype:value:store"),
		"subway_station" => elgg_echo("markertype:value:subway_station"),
		"synagogue" => elgg_echo("markertype:value:synagogue"),
		"taxi_stand" => elgg_echo("markertype:value:taxi_stand"),
		"train_station" => elgg_echo("markertype:value:train_station"),
		"university" => elgg_echo("markertype:value:university"),
		"veterinary_care" => elgg_echo("markertype:value:veterinary_care"),
		"zoo" => elgg_echo("markertype:value:zoo")
	);

	$marker_types = elgg_trigger_plugin_hook('markertype:value:s', 'all', $marker_types, $marker_types);

	return $marker_types;
}

function hj_maps_process_markers($entities = array(), $params = array()) {
	$user = elgg_get_logged_in_user_entity();
	if ($user) {
		$temp = new hjEntityLocation($user->guid);
		$user_position = $temp->getMapParams();
	} else {
		$user_position = array(
			'entity' => array(
				'guid' => rand(1000, 9999)
			)
		);
	}

	$site = elgg_get_site_entity();
	$temp_site = new hjEntityLocation($site->guid);
	$site_position = $temp_site->getMapParams();

	$clat = elgg_extract('clat', $params, get_input('clat'));
	$clong = elgg_extract('clong', $params, get_input('clong'));
	$useSessionLocation = elgg_extract('useSessionLocation', $params, get_input('useSessionLocation', true));

	foreach ($entities as $entity) {
		$marker = new hjEntityLocation($entity->guid);
		$markers[] = $marker->getMapParams();
	}

	if ($clat && $clong) {
		$center = array(
			'latitude' => $clat,
			'longitude' => $clong,
			'id' => rand(0, 100),
			'useSessionLocation' => $useSessionLocation
		);
	} else if (sizeof($markers) == 1) {
		$entity = $markers[0];
		$center = array(
			'latitude' => $entity['location']['latitude'],
			'longitude' => $entity['location']['longitude'],
			'id' => $entity['entity']['guid'],
			'useSessionLocation' => $useSessionLocation
		);
	} else if ($user->location || $user->temp_location) {
		$center = array(
			'latitude' => $user_position['location']['latitude'],
			'longitude' => $user_position['location']['longitude'],
			'id' => $user_position['entity']['guid'],
			'useSessionLocation' => $useSessionLocation
		);
	} else {
		$center = array(
			'latitude' => $site_position['location']['latitude'],
			'longitude' => $site_position['location']['longitude'],
			'id' => $site_position['entity']['guid'],
			'useSessionLocation' => $useSessionLocation
		);
	}


	$output = array('center' => $center, 'markers' => $markers, 'user' => $user_position);
	return $output;
}

function hj_maps_identify_map_center($user = null) {

	if (!$user) {
		$user = elgg_get_logged_in_user_entity();
	}
	if ($user) {
		$userlocation = new hjEntityLocation($user->guid);
	};

	if ($user && $user->temp_location) {
		$address = $user->temp_location;
		$latitude = $userlocation->getTempLatitude();
		$longitude = $userlocation->getTempLongitude();
	} else if ($_SESSION['location']) {
		$address = $_SESSION['location'];
		$latitude = $_SESSION['latitude'];
		$longitude = $_SESSION['longitude'];
	} else if ($user && $user->getLocation()) {
		$address = $user->getLocation();
		$latitude = $userlocation->getLatitude();
		$longitude = $userlocation->getLongitude();
	}
	if (!$latitude && !$longitude) {
		$address = $site->default_location;
		$site_location = new hjEntityLocation(elgg_get_site_entity()->guid);
		$latitude = $site_location->getLatitude();
		$longitude = $site_location->getLongitude();
	}

	$return = array(
		'address' => $address,
		'latitude' => $latitude,
		'longitude' => $longitude,
	);
	return $return;
}

