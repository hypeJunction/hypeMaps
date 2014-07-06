<?php

namespace hypeJunction\Maps;

if (!isset($vars['value']) || empty($vars['value'])) {
	$vars['value'] = 'default';
}
$vars['options_values'] = get_marker_types_options();

echo elgg_view('input/dropdown', $vars);