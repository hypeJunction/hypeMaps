<?php

$body .= elgg_view('input/text', array(
	'name' => "__map_q",
	'value' => get_input("__map_q", ''),
	'placeholder' => elgg_echo('hj:framework:filter:keywords'),
		));

// Reset all offsets so that lists return to first page
$query = elgg_parse_str(full_url());
foreach ($query as $key => $val) {
	if (strpos($key, '__off') === 0) {
		$footer .= elgg_view('input/hidden', array(
			'name' => $key,
			'value' => 0
				));
	}
}

if (HYPEMAPS_INTERFACE_VICINITY) {
	$type_subtype_pairs = hj_maps_get_mappable_type_subtype_pairs();

	foreach ($type_subtype_pairs as $type => $subtypes) {
		if (!is_array($subtypes)) {
			$subtypes = array($subtypes);
		}
		if (empty($subtypes)) {
			$subtypes = array('default');
		}
		foreach ($subtypes as $subtype) {
			if ($subtype != 'default') {
				$str = elgg_echo("item:$type:$subtype");
			} else {
				$str = elgg_echo("item:$type");
			}
			$options_values[$str] = "$type:$subtype";
		}
	}

	$body .= '<label>' . elgg_echo('hj:maps:filter:type_subtype_pairs') . '</label>';
	$body .= '<div>' . elgg_view('input/checkboxes', array(
		'name' => '__map_pairs',
		'value' => get_input('__map_pairs', $options_values),
		'options' => $options_values,
		'default' => false
			)) . '</div>';
}

$body .= elgg_view('dashboard/filter/list/extend', $vars);

$footer .= '<div class="hj-ajax-loader hj-loader-indicator hidden"></div>';

$footer .= elgg_view('input/submit', array(
	'value' => elgg_echo('filter'),
		));

$footer .= elgg_view('input/reset', array(
	'value' => elgg_echo('reset'),
	'class' => 'elgg-button-reset'
		));

$filter = elgg_view_module('form', '', $body, array(
	'footer' => $footer
		));

echo '<div class="hj-framework-list-filter">';

echo elgg_view('input/form', array(
	'method' => 'GET',
	'action' => '',
	'disable_security' => true,
	'body' => $filter,
	'class' => 'float-alt'
));
echo '</div>';