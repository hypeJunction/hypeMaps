<?php

$list_id = elgg_extract('list_id', $vars, "locations");
$getter_options = elgg_extract('getter_options', $vars);

$list_type = get_input("__list_type_$list_id", 'map');

$filter_vars = array(
	'handler' => 'maps'
);

$filter_vars = array_merge($vars, $filter_vars);

$list_options = array(
	'list_type' => $list_type,
	'list_class' => 'hj-maps-locations',
	'list_view_options' => array(
		'table' => array(
			'head' => array(
				'icon' => array(
					'text' => '',
					'sortable' => false,
					'override_view' => 'framework/maps/marker'
				),
				'item_details' => array(
					'colspan' => array(
						'title' => array(
							'text' => elgg_echo('hj:maps:table:title'),
							'sortable' => false,
						),
						'briefdescription' => array(
							'text' => '',
							'sortable' => false,
						)
					),
				),
				'location' => array(
					'text' => elgg_echo('hj:maps:table:location'),
					'sortable' => true,
					'sort_key' => 'md.location'
				),
				'distance' => array(
					'text' => elgg_echo('hj:maps:table:distance'),
					'sortable' => true,
					'sort_key' => 'maps.distance'
				),
				'menu' => array(
					'text' => '',
					'sortable' => false
				),
			)
		),
		'map' => array(
			'show_location_filter' => false
		)
	),
	'size' => 'small',
	'pagination' => true,
	'filter' => elgg_view('framework/maps/filters/list', $filter_vars)
);

$viewer_options = array(
	'full_view' => false,
	'list_type' => $list_type
);

if (!get_input("__lim_$list_id", false)) {
	set_input("__lim_$list_id", 50);
}

$content = hj_framework_view_list($list_id, $getter_options, $list_options, $viewer_options, 'elgg_get_entities');

echo $content;