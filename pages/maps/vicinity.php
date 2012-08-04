<?php

$type = get_input('type', null);
$subtype = get_input('subtype', null);
$owner = get_input('owner_guid', null);
$container = get_input('container_guid', null);
$markertype = get_input('markertype', null);

if ($type)
	$type = explode(',', $type);
if ($subtype) {
	$subtype = explode(',', $subtype);
	if (!in_array('object', $type)) {
		$type[] = 'object';
	}
}
if ($owner)
	$owner = explode(',', $owner);
if ($container)
	$container = explode(',', $container);
if ($markertype)
	$markertype = explode(',', $markertype);
$limit = get_input('limit', 10);
$offset = get_input('offset', 0);

$username = get_input('username', false);
if ($username) {
	$user = get_user_by_username();
} else {
	$user = elgg_get_logged_in_user_entity();
}

$center_params = hj_maps_identify_map_center($user);
$address = $center_params['address'];
$latitude = $center_params['latitude'];
$longitude = $center_params['longitude'];

$title = elgg_echo('hj:maps:vicinity');

$form = hj_framework_get_data_pattern('object', 'hjplace');
$data_options = array(
	'form_guid' => $form->guid,
	'fbox_x' => 500,
	'target' => 'hj-map-vicinity',
	'event' => 'create'
);

$data_options = hj_framework_extract_params_from_params($data_options);

if (elgg_is_logged_in()) {
	elgg_register_menu_item('title', array(
		'name' => 'addnewplace',
		'title' => elgg_echo('hj:maps:addnew'),
		'text' => elgg_view('input/button', array('value' => elgg_echo('hj:maps:addnew'), 'class' => 'elgg-button-action')),
		'href' => "action/framework/entities/edit",
		'data-options' => htmlentities(json_encode(array('params' => $data_options)), ENT_QUOTES, 'UTF-8'),
		'is_action' => true,
		'rel' => 'fancybox',
		'id' => "hj-ajaxed-add-hjplace",
		'class' => "hj-ajaxed-add",
		'priority' => 400
	));
}

$title .= elgg_view_menu('title');

$db_prefix = elgg_get_config('dbprefix');
$list_params = array(
	'types' => $type,
	'subtypes' => $subtype,
	'owner_guids' => $owner,
	'container_guids' => $container,
	'limit' => $limit,
	'offset' => $offset,
	'selects' => array("(((acos(sin(($latitude*pi()/180)) * sin((msv1.string*pi()/180))+cos(($latitude*pi()/180)) * cos((msv1.string*pi()/180)) * cos((($longitude - msv2.string)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance"),
	//'wheres' => array("((((acos(sin(($latitude*pi()/180)) * sin((msv1.string*pi()/180))+cos(($latitude*pi()/180)) * cos((msv1.string*pi()/180)) * cos((($longitude - msv2.string)*pi()/180))))*180/pi())*60*1.1515*1.609344) > 5000) AND ((((acos(sin(($latitude*pi()/180)) * sin((msv1.string*pi()/180))+cos(($latitude*pi()/180)) * cos((msv1.string*pi()/180)) * cos((($longitude - msv2.string)*pi()/180))))*180/pi())*60*1.1515*1.609344) < 7000)"),
	'metadata_name_value_pairs' => array(
		array('name' => 'geo:lat', 'value' => '', 'operand' => "!="),
		array('name' => 'geo:long', 'value' => '', 'operand' => "!="),
		array('name' => 'markertype', 'value' => $markertype)
	),
	'order_by' => "distance ASC",
	'count' => true
);

$count = elgg_get_entities_from_metadata($list_params);
$list_params['count'] = false;

$entities = elgg_get_entities_from_metadata($list_params);

$map = elgg_view_entity_list($entities, array(
	'list_type' => 'geomap',
	'data-options' => $list_params,
	'sync' => true,
	'pagination' => true,
	'position' => 'after',
	'base_url' => 'places/sync',
	'list_class' => 'hj-geomap-list',
	'count' => $count,
	'autorefresh' => false,
	'limit' => 10,
	'limit_prev' => $limit,
	'offset' => $offset,
	'class' => 'hj-view-list',
	'list_id' => 'hj-map-vicinity',
	'map_params' => array('useSessionLocation' => get_input('useSessionLocation', true))
		));

$find_location = elgg_view('hj/maps/changelocation', array('address' => $address, 'rel' => 'hj-map-vicinity'));
if (elgg_is_logged_in()) {
	$set_default_location = elgg_view('hj/maps/setdefaultlocation', array('address' => $user->temp_location, 'rel' => 'hj-map-vicinity'));
}

$location_forms = '<div class="hj-maps-location-forms">' . elgg_view_layout('hj/dynamic', array(
			'grid' => array(6, 6),
			'content' => array($find_location, $set_default_location)
		)) . '</div>';

//$sidebar = elgg_view('hj/maps/sidebar', array('markers' => $entities));
$module = elgg_view_module('aside', $title, $location_forms . $map);

$filter = elgg_view('hj/maps/filter');
$filter_module = elgg_view_module('aside', elgg_echo('hj:maps:filter'), $filter);

$body = elgg_view_layout('one_column', array(
	'content' => $module . $filter_mdoule,
//    'sidebar' => $sidebar,
		));

//$body = elgg_view_layout('one_column', array('content' => $content));

echo elgg_view_page($title, $body);
