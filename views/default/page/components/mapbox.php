<?php

/**
 * View items on the map
 *
 * @uses $vars['items']       Array of ElggEntity or ElggAnnotation objects
 * @uses $vars['offset']      Index of the first list item in complete list
 * @uses $vars['limit']       Number of items per page. Only used as input to pagination.
 * @uses $vars['count']       Number of items in the complete list
 * @uses $vars['base_url']    Base URL of list (optional)
 * @uses $vars['pagination']  Show pagination? (default: true)
 * @uses $vars['position']    Position of the pagination: before, after, or both
 * @uses $vars['full_view']   Show the full view of the items (default: false)
 * @uses $vars['list_class']  Additional CSS class for the <ul> element
 * @uses $vars['item_class']  Additional CSS class for the <li> elements
 */

namespace hypeJunction\Maps;

elgg_load_css('maps');

elgg_load_js('jquery.sticky-kit');
elgg_load_js('jquery.form');
elgg_load_js('google.maps');
elgg_load_js('maps.mapbox');

elgg_push_context('mapbox');

$map = elgg_extract('list', $vars);
if (!$map instanceof ElggMap) {
	return;
}

$items = $map->getItems();
$options = $map->getOptions();
$count = $map->getCount();

$offset = elgg_extract('offset', $options);
$limit = elgg_extract('limit', $options);
$base_url = elgg_extract('base_url', $options, '');
$pagination = elgg_extract('pagination', $options, true);
$offset_key = elgg_extract('offset_key', $options, 'offset');
$position = elgg_extract('position', $options, 'after');

$list_class = 'elgg-list maps-items';
if (isset($options['list_class'])) {
	$list_class = "$list_class {$options['list_class']}";
}

$item_class = 'elgg-item';
if (isset($options['item_class'])) {
	$item_class = "$item_class {$options['item_class']}";
}

if ($pagination && $count) {
	$nav = elgg_view('navigation/pagination', array(
		'base_url' => $base_url,
		'offset' => $offset,
		'count' => $count,
		'limit' => $limit,
		'offset_key' => $offset_key,
	));
}

echo '<div class="maps-container maps-list">';

$mapbox_attrs = $map->getMapboxAttributes();
$mapbox_attrs = elgg_format_attributes($mapbox_attrs);

echo elgg_view('output/url', array(
	'href' => 'javascript:void(0);',
	'text' => elgg_echo('maps:findme'),
	'class' => 'maps-find-me',
));

echo "<div $mapbox_attrs></div>";

if ($position == 'before' || $position == 'both') {
	echo $nav;
}

echo "<ul class=\"$list_class\">";

foreach ($items as $item) {

	$has_items = true;

	$view = elgg_view_list_item($item, $options);

	if (!$view) {
		continue;
	}

	$item_attrs = $map->getItemAttributes($item);

	$proximity = '';
	if (!is_null($item_attrs['data-proximity'])) {
		$proximity = elgg_view('output/maps/proximity', array(
			'value' => $item_attrs['data-proximity'],
			'class' => 'elgg-text-help maps-item-proximity'
		));
		$view .= $proximity;
	}

	$pin = '&nbsp;';
	if ($item_attrs['data-pin']) {
		$pin = elgg_view('output/maps/pin', array(
			'value' => $item_attrs['data-pin'],
			'class' => 'maps-item-pin',
			'alt' => $item_attrs['data-title'],
			'title' => ($proximity) ? elgg_echo('maps:proximity:info', array(strip_tags($proximity), $map->getLocation())) : $item_attrs['data-title'],
		));
	}

	$view = elgg_view_image_block($pin, $view, array(
		'class' => 'maps-item-pin-block'
	));

	$id = (elgg_instanceof($item)) ? "elgg-{$item->getType()}-{$item->getGUID()}" : "item-{$item->getType()}-{$item->id}";
	$item_attrs['id'] = $id;
	$item_attrs['class'] = $item_class;
	$item_attrs = elgg_format_attributes($item_attrs);

	echo "<li $item_attrs>$view</li>";
}

if (!$has_items) {
	echo '<li class="placeholder">' . elgg_echo('maps:empty') . '</li>';
}

echo '</ul>';

if ($position == 'after' || $position == 'both') {
	echo $nav;
}

echo '</div>';

elgg_pop_context();
