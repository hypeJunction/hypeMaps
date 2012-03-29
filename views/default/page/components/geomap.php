<?php

/**
 * View a list of items
 *
 * @package Elgg
 *
 * @uses $vars['items']       Array of ElggEntity or ElggAnnotation objects
 * @uses $vars['offset']      Index of the first list item in complete list
 * @uses $vars['limit']       Number of items per page
 * @uses $vars['count']       Number of items in the complete list
 * @uses $vars['base_url']    Base URL of list (optional)
 * @uses $vars['pagination']  Show pagination? (default: true)
 * @uses $vars['position']    Position of the pagination: before, after, or both
 * @uses $vars['full_view']   Show the full view of the items (default: false)
 * @uses $vars['list_class']  Additional CSS class for the <ul> element
 * @uses $vars['item_class']  Additional CSS class for the <li> elements
 * @uses $vars['inverse_order']  Is this list in an inversed order in relation to data_options
 * $uses $vars['data-options'] An array of options that was used to render the items
 */

$items = $vars['items'];
$offset = elgg_extract('offset', $vars);
$limit = elgg_extract('limit', $vars);
$limit_prev = elgg_extract('limit_prev', $vars);
$count = elgg_extract('count', $vars);
$base_url = elgg_extract('base_url', $vars, '');
$pagination = elgg_extract('pagination', $vars, true);
$offset_key = elgg_extract('offset_key', $vars, 'offset');
$position = elgg_extract('position', $vars, 'after');
$list_class = 'elgg-list';
$list_id = elgg_extract('list_id', $vars, null);
$inverse_order = elgg_extract('inverse_order', $vars, null);
$data_options = elgg_extract('data-options', $vars, false);
$map_params = elgg_extract('map_params', $vars);
$autorefresh = elgg_extract('autorefresh', $vars, true);

if (is_array($data_options)) {
	$list_class = "$list_class hj-syncable";
}

if (isset($vars['list_class'])) {
	$list_class = "$list_class {$vars['list_class']}";
}

$item_class = 'elgg-item';
if (isset($vars['item_class'])) {
	$item_class = "$item_class {$vars['item_class']}";
}

$html = "";
$nav = "";

if ($data_options['type'] && $data_options['subtype']) {
	$pagination_str = elgg_echo("items:{$data_options['type']}:{$data_options['subtype']}");
}
$pagination_options = array(
	'baseurl' => $base_url,
	'offset' => $offset,
	'count' => $count,
	'limit' => $limit, // this is a limit for how many items to load on refresh / pagination
	'limit_prev' => $limit_prev, // this is a limit that was used to render the initial list
	'string' => $pagination_str, // comes in handy when rendering a language string for show all/ show next
	'offset_key' => $offset_key,
	'list_id' => $list_id,
	'inverse_order' => $inverse_order,
	'autorefresh' => $autorefresh

);

if ($pagination && $count) {
	$pagination_options['ajaxify'] = false;
	if (is_array($data_options)) {
		$pagination_options['ajaxify'] = true;
	}

	$nav .= elgg_view('navigation/pagination', $pagination_options);
	$stats['nav'] = $nav;
}

$before = elgg_view('page/components/list/prepend', $vars);
$after = elgg_view('page/components/list/append', $vars);

$list_params = array('items', 'offset', 'limit', 'count', 'base_url', 'pagination', 'offset_key', 'position', 'list_class', 'list_id', 'data-options');
foreach ($list_params as $list_param) {
	if (isset($vars[$list_param])) {
		unset($vars[$list_param]);
	}
}

$html .= $before;

$data_options_list_items = array();

if (is_array($items) && count($items) > 0) {
	foreach ($items as $key => $item) {
		$instance = false;
		if (!elgg_instanceof($item) && is_numeric($item)) {
			$item = get_entity($item);
		}
		if (elgg_instanceof($item) && !elgg_instanceof($item, 'site')) {
			$id = "elgg-object-{$item->getGUID()}";
			$data_options_list_items[] = $item->getGUID();
			$instance = true;
		} elseif ($item instanceof ElggRiverItem) {
			$id = "item-{$item->getType()}-{$item->id}";
			$time = $item->posted;
			$data_options_list_items[] = $item->id;
			$instance = true;
		}

		if ($instance) {
			$html .= "<li id=\"$id\" class=\"$item_class\">";
			$html .= elgg_view_list_item($item, $vars);
			$html .= '</li>';
		}		
	}
}

$html .= $after;

$html = "<ul id=\"$list_id\" class=\"$list_class hidden\">$html</ul>";

$width = elgg_extract('width', $map_params, '100%');
$height = elgg_extract('height', $map_params, '450px');

$geo_params = hj_maps_process_markers($items, $map_params);
$geo_params['container'] = $list_id;

$html .= <<<HTML
<div class="hj-ajaxed-map-static clearfix">
      <div id="map-container-$list_id" rel="$list_id" class="hj-map-full-page left" style="width:{$width};height:{$height};"></div>
</div>
HTML;

$stats['list_id'] = $list_id;

$stats_box = elgg_view('hj/maps/stats', $stats);
$html .= elgg_view('page/components/hj/wrappers/overlay_sidebar', array('content' => $stats_box));

$html = '<div class="hj-map-and-stats-wrapper">' . $html . '</div>';


//if ($position == 'before' || $position == 'both' && !$ajaxify) {
//	$html = $nav . $html;
//}
//
//if ($position == 'after' || $position == 'both') {
//	$html .= $nav;
//}


echo $html;

// We are storing details of this list in the window.hjdata object
// This gives us access to guids of elements that have been rendered and other data we can use in JS

if ($list_id) {
	$data = array(
		'lists' => array(
			"$list_id" => array(
				'options' => $data_options,
				'items' => $data_options_list_items,
				'pagination' => $pagination_options,
				'geo' => $geo_params
		)));
	$data = json_encode($data);
	$script = <<<___JS
		<script type="text/javascript">
			var new_list = $data;
			if (!window.hjdata) {
				window.hjdata = new Object();
			}
			window.hjdata = $.extend(true, window.hjdata, new_list);
		</script>
___JS;
	echo $script;
}