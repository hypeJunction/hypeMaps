<?php

/* hypeMaps
 *
 * Geolocation Support for hypeJunction Plugins
 * @package hypeJunction
 * @subpackage hypeMaps
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 * @copyright Copyrigh (c) 2011, Ismayil Khayredinov
 */

elgg_register_event_handler('init', 'system', 'hj_maps_init');

function hj_maps_init() {

	$plugin = 'hypeMaps';

	if (!elgg_is_active_plugin('hypeFramework')) {
		register_error(elgg_echo('hj:framework:disabled', array($plugin, $plugin)));
		disable_plugin($plugin);
	}

	$shortcuts = hj_framework_path_shortcuts($plugin);

	// Helper Classes
	elgg_register_classes($shortcuts['classes']);

	// Register Libraries
	elgg_register_library('hj:maps:setup', $shortcuts['lib'] . 'maps/setup.php');

	elgg_register_library('hj:places:base', $shortcuts['lib'] . 'places/base.php');
	elgg_load_library('hj:places:base');
	elgg_register_library('hj:places:setup', $shortcuts['lib'] . 'places/setup.php');

	//Check if the initial setup has been performed, if not porform it
	if (!elgg_get_plugin_setting('hj:maps:setup')) {
		elgg_load_library('hj:maps:setup');
		elgg_load_library('hj:places:setup');
		if (hj_maps_setup() && hj_places_setup())
			system_message('hypeMaps was successfully configured');
	}

	// Register Actions
	elgg_register_action('maps/getter', $shortcuts['actions'] . 'hj/maps/getter.php', 'public');
	elgg_register_action('maps/abstract', $shortcuts['actions'] . 'hj/maps/abstract.php', 'public');
	elgg_register_action('maps/setlocation', $shortcuts['actions'] . 'hj/maps/setlocation.php');
	elgg_register_action('maps/changelocation', $shortcuts['actions'] . 'hj/maps/changelocation.php');
	elgg_register_action('maps/filter', $shortcuts['actions'] . 'hj/maps/filter.php', 'public');

	// Register new admin menu item
	//elgg_register_admin_menu_item('administer', 'maps', 'hj', 300);
	// Register CSS and JS
	$css_url = elgg_get_simplecache_url('css', 'hj/maps/base');
	elgg_register_css('hj.maps.base', $css_url);
	elgg_load_css('hj.maps.base');

	$current_language = get_current_language();
	$google_url = "http://maps.googleapis.com/maps/api/js?libraries=geometry,adsense&sensor=true&language={$current_language}&output=svembed";
	elgg_register_js('hj.maps.google', $google_url);

	$google_gear_url = 'http://code.google.com/apis/gears/gears_init.js';
	elgg_register_js('hj.maps.googlegears', $google_gear_url);

	$js_url = elgg_get_simplecache_url('js', 'hj/maps/base');
	elgg_register_js('hj.maps.base', $js_url);

	// Add hypeFormBuilder Field Types and processing algorithms
	elgg_register_plugin_hook_handler('hj:formbuilder:fieldtypes', 'all', 'hj_maps_location_input');
	elgg_register_plugin_hook_handler('hj:framework:field:process', 'all', 'hj_maps_location_input_process');

	// Add custom markertypes from plugin settings
	elgg_register_plugin_hook_handler('hj:maps:markertypes', 'all', 'hj_maps_custom_markertypes');

	// Geolocate user's location on profile update
	elgg_register_event_handler('profileupdate', 'user', 'hj_maps_geocode_user_location');

	if (elgg_is_logged_in()) {
		$user = elgg_get_logged_in_user_entity();
		if (!$user->location) {
			hj_maps_geocode_user_location('profileupdate', 'user', $user);
			system_message(elgg_echo('hj:maps:locationchangedtodefault', array(elgg_get_plugin_setting('default_location', 'hypeMaps'))));
		}
	}

	/**
	 * PLACES
	 */
	elgg_register_page_handler('places', 'hj_maps_page_handler');

	if (elgg_is_logged_in()) {
		elgg_register_menu_item('site', array(
			'name' => 'maps',
			'text' => elgg_echo('hj:maps:places'),
			'href' => 'places/all',
			'priority' => 400
		));
	}
	elgg_register_entity_url_handler('object', 'hjplace', 'hj_places_url_forwarder');

	// Register new profile menu item
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'hj_places_owner_block_menu');

	// Register new sidebar menu
	elgg_register_plugin_hook_handler('register', 'menu:page', 'hj_maps_places_owner_block_menu');

	elgg_register_plugin_hook_handler('register', 'menu:hjentityhead', 'hj_maps_places_entity_head_menu');
}

function hj_maps_location_input($hook, $type, $return, $params) {
	$return[] = 'address';
	$return[] = 'location';
	$return[] = 'coordinates';

	/** @todo: add support for other input types */
	//$return[] = 'map_route';
	//$return[] = 'map_area';

	return $return;
}

function hj_maps_location_input_process($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params, false);
	$field = elgg_extract('field', $params, false);
	if (!$entity || !$field) {
		return true;
	}

	switch ($field->input_type) {
		case 'address' :
			$field_name = $field->name;

			$address['street1'] = get_input('address_street1');
			$address['street2'] = get_input('address_street2');
			$address['city'] = get_input('address_city');
			$address['province'] = get_input('address_province');
			$address['postal_code'] = get_input('address_postal_code');
			$address['country'] = get_input('address_country');

			$format = new hjLocation();
			$format_string = $format->getAddressString($address);

			$location = new hjEntityLocation($entity->guid);
			$location->setAddressMetadata($address);
			$location->setEntityLocation($address);

			if ($field_name !== 'location') {
				$entity->$field_name = $entity->guid;
			}
			break;

		case 'location' :
			$field_name = $field->name;

			$address = get_input($field_name);

			$location = new hjEntityLocation($entity->guid);
			$location->setAddressMetadata($address);
			$location->setEntityLocation($address);

			if ($field_name !== 'location') {
				$entity->$field_name = $entity->guid;
			}
			break;

		case 'coordinates' :
			$field_name = $field->name;

			$lat = trim(str_replace(',', '.', get_input('latitude')));
			$long = trim(str_replace(',', '.', get_input('longitude')));

			$latlong = new hjLatLong($lat, $long);

			$address = new hjLocation();
			$address = $address->getReverseGeoCode($latlong);

			$location = new hjEntityLocation($entity->guid);
			$location->setEntityCoords($address, $latlong);
			$location->setLocation($address);

			if ($field_name !== 'location') {
				$entity->$field_name = $entity->guid;
			}
			break;
	}

	return true;
}

function hj_maps_geocode_user_location($event, $type, $entity) {
	if (elgg_instanceof($entity, 'user')) {
		$location = new hjEntityLocation($entity->guid);
		if (!$entity->location) {
			$entity->location = elgg_get_plugin_setting('default_location', 'hypeMaps');
		}
		$location->setAddressMetadata($entity->location);
		$location->setEntityLocation($entity->location);
	}
	return true;
}

function hj_maps_page_handler($page) {
	elgg_load_js('hj.comments.base');
	elgg_load_css('hj.comments.bar');
	elgg_load_js('hj.framework.ajax');
	elgg_load_js('hj.framework.fieldcheck');

	elgg_load_js('hj.maps.base');
	elgg_load_js('hj.maps.google');
	elgg_load_js('hj.maps.googlegears');

	$plugin = 'hypeMaps';
	$shortcuts = hj_framework_path_shortcuts($plugin);
	$pages = $shortcuts['pages'] . 'maps/';
	elgg_push_breadcrumb(elgg_echo('hj:maps:places'));

	// Check if the username was provided in the url
	// If no username specified, display logged in user's portfolio

	$type = elgg_extract(0, $page, 'owner');

	switch ($type) {
		case 'vicinity' :
		case 'all' :
		default :
			set_input('useSessionLocation', true);
			include "{$pages}vicinity.php";
			break;

		case 'point' :
			$entity_guid = elgg_extract(1, $page, 0);
			$entity = get_entity($entity_guid);
			if (elgg_instanceof($entity) && $entity->getLocation()) {
				set_input('e', $page[1]);
				include "{$pages}point.php";
			} else {
				register_error('hj:maps:error:nolocationspecified');
				forward(REFERER);
			}
			break;

		case 'all' :
			set_input('useSessionLocation', true);
			include "{$pages}vicinity.php";
			break;

		case 'marker' :
			$guid = elgg_extract(1, $page, 0);
			$size = elgg_extract(2, $page, 'tiny');
			set_input('e', $guid);
			set_input('size', $size);
			include "{$pages}marker.php";
			break;

		case 'owner' :
			set_input('useSessionLocation', true);
			$owner = elgg_extract(1, $page, elgg_get_logged_in_user_entity()->username);
			set_input('username', $owner);
			include "{$pages}owner.php";
			break;
	}
	return true;
}

function hj_maps_custom_markertypes($hook, $type, $return, $params) {
	if ($marker_types = elgg_get_plugin_setting('markertypes', 'hypeMaps')) {
		$marker_types = explode(',', $marker_types);
		foreach ($marker_types as $key => $value) {
			$sanitized = str_replace(' ', '_', trim($value));
			$custom_marker_types[$sanitized] = elgg_echo('markertype:value:' . $sanitized);
		}
		$return = $custom_marker_types;
	}
	return $return;
}

function hj_places_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "places/owner/{$params['entity']->username}";
		$return[] = new ElggMenuItem('places', elgg_echo('hj:maps:places:menu:owner_block'), $url);
		return $return;
	}
	return false;
}

function hj_maps_places_owner_block_menu($hook, $type, $return, $params) {

	if ($params['context'] == 'places') {
		$all = array(
			'name' => 'all',
			'title' => elgg_echo('hj:maps:allplaces'),
			'text' => elgg_echo('hj:maps:allplaces'),
			'href' => "places/all",
			'priority' => 500
		);
		$return[] = ElggMenuItem::factory($all);

		$mine = array(
			'name' => 'mine',
			'title' => elgg_echo('hj:maps:mine'),
			'text' => elgg_echo('hj:maps:mine'),
			'href' => "places/owner",
			'priority' => 600
		);
		$return[] = ElggMenuItem::factory($mine);

//        $friends = array(
//            'name' => 'friends',
//            'title' => elgg_echo('hj:maps:friends'),
//            'text' => elgg_echo('hj:maps:friends'),
//            'href' => "places/friends",
//            'priority' => 700
//        );
		$return[] = ElggMenuItem::factory($friends);
	}
	return $return;
}

function hj_places_url_forwarder($entity) {
	return "places/point/$entity->guid";
}

function hj_maps_places_entity_head_menu($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	$handler = elgg_extract('handler', $params);
	$data = hj_framework_json_query($params);

	if (elgg_in_context('print') || elgg_in_context('activity')) {
		return $return;
	}

	if (elgg_instanceof($entity, 'object', 'hjplace')) {
		$action = "action/maps/getter?e=$entity->guid";
		$fullview = array(
			'name' => 'fullview',
			'title' => elgg_echo('hj:maps:showmap'),
			'text' => elgg_view_icon('hj hj-icon-location'),
			'href' => $action,
			'is_action' => true,
			'rel' => 'fancybox',
			'data-options' => $data,
			'class' => "hj-ajaxed-map-single-popup",
			'id' => "hj-entity-map-popup-$entity->guid",
		);
		$return[] = ElggMenuItem::factory($fullview);
	}
	return $return;
}