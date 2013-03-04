<?php

elgg_load_js('maps.google.js');
elgg_load_js('maps.base.js');
elgg_load_css('maps.base.css');

elgg_push_context('map-view');

$list_id = elgg_extract('list_id', $vars);
$entities = elgg_extract('entities', $vars);

$list_options = elgg_extract('list_options', $vars);

$viewer_options = elgg_extract('viewer_options', $vars);
$vars = array_merge($vars, $viewer_options);

$class = "hj-map-wrapper";
$item_class = trim("elgg-item " . elgg_extract('item_class', $list_options, ''));

if (isset($list_options['list_class'])) {
	$class = "$class {$list_options['list_class']}";
}

if (is_array($entities) && count($entities) > 0) {

	foreach ($entities as $entity) {
		$vars['entity'] = $entity;
		$vars['class'] = $item_class;
		$list_body .= elgg_view('page/components/grids/elements/map/item', $vars);
		$onthemap .= elgg_view('page/components/grids/elements/map/anchor', $vars);
	}

} else {
	$onthemap = elgg_view('page/components/grids/elements/map/placeholder', array(
		'class' => $item_class,
		'data-uid' => -1,
		'data-ts' => time()
			));
}

$list_params = array(
	'id' => $list_id,
	'class' => $class
);

if (isset($list_options['list_view_options']['map'])) {
	$map_options = $list_options['list_view_options']['map'];
}

$list_params['data-lat'] = ($map_options['center']['latitude']) ? $map_options['center']['latitude'] : HYPEMAPS_LAT;
$list_params['data-long'] = ($map_options['center']['longitude']) ? $map_options['center']['longitude'] : HYPEMAPS_LAT;

if ($map_options['width']) {
	$style .= "width:{$map_options['width']}px;";
} else {
	$style .= "width:100%;";
}

if ($map_options['height']) {
	$style .= "height:{$map_options['height']}px;";
} else {
	$style .= "height:500px;";
}

$list_params['style'] = $style;

$list_params = elgg_format_attributes($list_params);

if ($map_options['show_onthemap'] === false) {
	$onthemap = '';
} else {
	$onthemap = elgg_view('page/components/grids/elements/map/onthemap', array(
		'content' => $onthemap
			));
}
if ($map_options['show_location_filter'] !== false) {
	$location_filter = elgg_view('page/components/grids/elements/map/filter', $vars);
}

$list_head = elgg_view('page/components/grids/elements/map/head', $vars);

$list = "$list_head$location_filter<div class=\"hj-framework-map-view\">$placeholder<ul $list_params>$list_body</ul>$onthemap</div>";

$show_pagination = elgg_extract('pagination', $list_options, true);

$pagination_type = elgg_extract('pagination_type', $list_options, 'paginate');

if ($show_pagination) {
	$pagination = elgg_view("page/components/grids/elements/pagination/$pagination_type", $vars);
}

$pagination = '<div class="hj-framework-list-pagination-wrapper row-fluid">' . $pagination . '</div>';
$position = elgg_extract('pagination_position', $list_options, 'after');

if ($position == 'both') {
	$list = "$pagination $list $pagination";
} else if ($position == 'before') {
	$list = "$pagination $list";
} else {
	$list = "$list $pagination";
}

echo $list;

elgg_pop_context();