<?php

elgg_register_plugin_hook_handler('init', 'form:edit:plugin:hypemaps', 'hj_maps_init_plugin_settings_form');
elgg_register_plugin_hook_handler('init', 'form:edit:object:hjplace', 'hj_maps_init_place_form');

elgg_register_plugin_hook_handler('process:input', 'form:input:type:address', 'hj_maps_process_address_input');

function hj_maps_init_plugin_settings_form($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	$config['fields']['params[default_location]'] = array(
		'value' => $entity->default_location,
		'hint' => elgg_echo('edit:plugin:hypemaps:hint:default_location')
	);
	$config['fields']['params[metric_system]'] = array(
		'input_type' => 'dropdown',
		'value' => $entity->metric_system,
		'options_values' => array(
			'SI' => elgg_echo('hj:maps:settings:SI'),
			'US' => elgg_echo('hj:maps:settings:US')
		),
		'hint' => elgg_echo('edit:plugin:hypemaps:hint:metric_system')
	);

	$settings = array(
		'interface_location',
		'interface_places',
		'interface_vicinity',
	);

	if (HYPEMAPS_INTERFACE_PLACES) {
		$settings[] = 'places_river';
		$settings[] = 'places_cover';
		$settings[] = 'group_places';
		$settings[] = 'summary_map';
	}

	$settings[] = 'link_map';

	foreach ($settings as $s) {
		$config['fields']["params[$s]"] = array(
			'input_type' => 'dropdown',
			'options_values' => array(
				0 => elgg_echo('disable'),
				1 => elgg_echo('enable')
			),
			'value' => $entity->$s,
			'hint' => elgg_echo("edit:plugin:hypemaps:hint:$s")
		);
	}

	if (HYPEMAPS_INTERFACE_VICINITY) {
		// Types and subtypes to map
		$dbprefix = elgg_get_config('dbprefix');
		$data = get_data("SELECT e.type AS type, e.subtype AS subtype_id
								FROM {$dbprefix}entities e
								JOIN {$dbprefix}metadata md ON e.guid = md.entity_guid
								JOIN {$dbprefix}metastrings msn ON md.name_id = msn.id AND msn.string = 'geo:lat'
								JOIN {$dbprefix}metastrings msv ON md.value_id = msv.id AND msv.string IS NOT NULL
								GROUP BY e.subtype");

		foreach ($data as $r) {
			$type = $r->type;
			$subtype = get_subtype_from_id($r->subtype_id);
			if ($subtype) {
				$str = elgg_echo("item:$type:$subtype");
				$subtype_options[$str] = "$type:$subtype";
			} else {
				$str = elgg_echo("item:$type");
				$subtype_options[$str] = "$type:default";
			}
		}

		$config['fields']["params[map_type_subtype_pairs]"] = array(
			'input_type' => 'checkboxes',
			'default' => false,
			'value' => explode(',', $entity->map_type_subtype_pairs),
			'options' => $subtype_options,
			'hint' => elgg_echo('edit:plugin:hypemaps:hint:map_type_subtype_pairs')
		);
	}

	$default_types = array('default', 'airport', 'amusement_park', 'aquarium', 'art_gallery', 'atm', 'bakery', 'bank', 'bar', 'beauty_salon', 'bicycle_store', 'bowling_alley', 'bus_station', 'cafe', 'campground', 'car_dealer', 'car_rental', 'car_repair', 'car_wash', 'casino', 'cemetery', 'church', 'city_hall', 'clothing_store', 'convenience_store', 'courthouse', 'dentist', 'department_store', 'electrician', 'electronics_store', 'embassy', 'finance', 'fire_station', 'florist', 'food', 'furniture_store', 'gas_station', 'government_office', 'gym', 'hardware_store', 'hospital', 'jewelry_store', 'laundry', 'lawyer', 'library', 'liquor_store', 'lodging', 'mosque', 'movie_rental', 'movie_theater', 'museum', 'night_club', 'park', 'parking', 'pharmacy', 'police', 'post_office', 'restaurant', 'school', 'shoe_store', 'shopping_mall', 'spa', 'stadium', 'store', 'subway_station', 'synagogue', 'taxi_stand', 'train_station', 'university', 'veterinary_care', 'zoo');
	$config['fields']['params[markertypes]'] = array(
		'value' => (isset($entity->markertypes) && !empty($entity->markertypes)) ? $entity->markertypes : implode(',', $default_types),
		'hint' => elgg_echo('edit:plugin:hypemaps:hint:markertypes')
	);

	$config['buttons'] = false;

	return $config;
}

function hj_maps_init_place_form($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params, null);
	$container_guid = ($entity) ? $entity->container_guid : elgg_extract('container_guid', $params, ELGG_ENTITIES_ANY_VALUE);
	$container = get_entity($container_guid);

	$config = array(
		'attributes' => array(
			'enctype' => 'multipart/form-data',
			'id' => 'form-edit-object-hjplace',
			'action' => 'action/edit/object/hjplace'
		),
		'fields' => array(
			'type' => array(
				'input_type' => 'hidden',
				'value' => 'object'
			),
			'subtype' => array(
				'input_type' => 'hidden',
				'value' => 'hjplace'
			),
			'title' => array(
				'value' => ($entity) ? $entity->title : '',
				'required' => true,
				'label' => elgg_echo('hj:label:hjplace:title')
			),
			'description' => array(
				'value' => ($entity) ? $entity->description : '',
				'input_type' => 'longtext',
				'class' => 'elgg-input-longtext',
				'label' => elgg_echo('hj:label:hjplace:description')
			),
			'markertype' => array(
				'input_type' => 'dropdown',
				'value' => $entity->markertype,
				'options_values' => hj_maps_get_marker_types_options(),
				'label' => elgg_echo('hj:label:hjplace:markertype'),
				'required' => true
			),
			'location' => array(
				'input_type' => 'location',
				'value' => ($entity) ? $entity->getLocation() : '',
				'label' => elgg_echo('hj:label:hjplace:location'),
				'required' => true
			),
//			'address' => array(
//				'input_type' => 'address',
//				'label' => elgg_echo('hj:label:hjplace:location'),
//				'required' => true,
//				'entity' => $entity
//			),
			'cover' => (HYPEMAPS_PLACES_COVER) ? array(
				'value' => ($entity),
				'input_type' => 'entity_icon'
					) : NULL,
			'tags' => array(
				'input_type' => 'tags',
				'value' => $entity->tags,
				'label' => elgg_echo('hj:label:hjalbumimage:tags')
			),
			'access_id' => array(
				'value' => ($entity) ? $entity->access_id : ACCESS_PUBLIC,
				'input_type' => 'hidden'
			),
			'add_to_river' => (HYPEFORUM_TOPIC_RIVER) ? array(
				'input_type' => 'hidden',
				'value' => ($entity) ? false : true
					) : null
		)
	);

	return $config;
}

function hj_maps_process_address_input($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params, false);

	if (!elgg_instanceof($entity)) {
		return false;
	}

	$name = elgg_extract('name', $params, 'address');

	$value = get_input($name, '');

	if (is_array($value)) {
		foreach ($value as $key => $val) {
			$entity->$key = $val;
			if ($val && !empty($val)) {
				$location[] = $val;
			}
		}
		$entity->location = implode(', ', $location);
	}

	return true;
}