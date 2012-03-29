<?php

if (elgg_is_xhr()) {
	$data = get_input('listdata');
	$sync = elgg_extract('sync', $data, 'new');
	$items = elgg_extract('items', $data, 0);
	$inverse_order = elgg_extract('inverse_order', $data['pagination'], false);
	if ($inverse_order == 'null') {
		$inverse_order = false;
	}

	$options = elgg_extract('options', $data, array());
	array_walk_recursive($options, 'hj_framework_decode_options_array');

	$limit = elgg_extract('limit', $data['pagination'], 10);

	$defaults = array(
		'limit' => (int) $limit,
		'pagination' => TRUE,
		'class' => 'hj-syncable-list'
	);
	$options['offset'] = sizeof($items);

	$options = array_merge($defaults, $options);
	$items = elgg_get_entities_from_metadata($options);

	if (is_array($items) && count($items) > 0) {
		foreach ($items as $key => $item) {
			$id = "elgg-object-{$item->guid}";
			$html = "<li id=\"$id\" class=\"elgg-item\">";
			$html .= elgg_view_list_item($item);
			$html .= '</li>';

			$location = new hjEntityLocation($item->guid);
			$geo = $location->getMapParams();

			$output[] = array('guid' => $item->guid, 'html' => $html, 'geo' => $geo);
		}
	}
	header('Content-Type: application/json');
	print(json_encode(array('output' => $output)));
	exit;
}

forward(REFERER);